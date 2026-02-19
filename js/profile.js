// js/profile.js - Versão com atualização dinâmica de listas

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS carregado!');

    // Elementos
    var modal = document.getElementById('followers-modal');
    var modalBackdrop = document.getElementById('modal-backdrop');
    var modalCloseBtn = document.getElementById('modal-close-btn');
    var statLinks = document.querySelectorAll('.stat-item[data-tab]');
    var modalTabs = document.querySelectorAll('.modal-tab-link');
    var modalContents = document.querySelectorAll('.modal-tab-content');
    var modalTitle = document.getElementById('modal-title');
    var followBtn = document.getElementById('followBtn');
    var followersCountEl = document.getElementById('followersCount');
    var followingCountEl = document.getElementById('followingCount');
    var messageDiv = document.getElementById('message');
    var currentViewingId = null; // ID do perfil visualizado (será definido ao abrir modal)

    // Função para abrir o modal
    function openModal(viewingId) {
        currentViewingId = viewingId;
        if (modal) modal.style.display = 'flex';
    }

    // Função para fechar o modal
    function closeModal() {
        if (modal) modal.style.display = 'none';
        currentViewingId = null;
    }

    // Função para ativar uma aba específica e carregar lista
    function activateTab(tabName, viewingId) {
        modalTabs.forEach(function(tab) {
            var isActive = tab.getAttribute('data-tab') === tabName;
            tab.classList.toggle('active', isActive);
        });

        modalContents.forEach(function(content) {
            var isActive = content.id.indexOf(tabName) !== -1;
            content.style.display = isActive ? 'block' : 'none';
        });

        if (modalTitle) {
            modalTitle.textContent = tabName === 'followers' ? 'Seguidores' : 'Seguindo';
        }

        // Carregar lista dinâmica
        loadFollowList(tabName, viewingId);
    }

    // Função para carregar lista via XMLHttpRequest
    function loadFollowList(type, viewingId) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'src/actions/get_follow_lists.php?user_id=' + viewingId + '&type=' + type, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.error) {
                        console.error('Erro:', data.error);
                        return;
                    }
                    renderFollowList(type, data.list);
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                }
            } else {
                console.error('Erro na requisição:', xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Erro de rede');
        };
        xhr.send();
    }

    // Função para renderizar a lista no DOM
    function renderFollowList(type, list) {
        var container = document.getElementById(type + '-content');
        if (!container) return;

        // Limpar conteúdo anterior
        container.innerHTML = '';

        if (list.length === 0) {
            container.innerHTML = '<p style="text-align: center; padding: 20px;">' + (type === 'followers' ? 'Nenhum seguidor ainda.' : 'Não seguindo ninguém ainda.') + '</p>';
            return;
        }

        // Renderizar cada usuário
        list.forEach(function(user) {
            var userAvatar = user.profile_image || user.avatar || 'uploads/default.png';
            var isFollowingThis = false; // Você pode implementar uma verificação adicional se necessário

            var itemHtml = '<div class="user-list-item">' +
                '<div class="user-info-flex">' +
                '<img src="' + userAvatar + '" alt="Avatar" class="avatar-md" onerror="this.src=\'uploads/default.png\'">' +
                '<div>' +
                '<span class="user-name-list">' + user.nome + '</span>' +
                '<span class="user-handle-list">@' + user.nome.replace(/\s+/g, '').toLowerCase() + '</span>' +
                '</div>' +
                '</div>';
            
            // Adicionar botão de seguir apenas se não for o próprio usuário (ajuste conforme necessário)
            if (user.id !== currentViewingId) { // Ajuste para ID do usuário logado se disponível
                itemHtml += '<button class="follow-button-list ' + (isFollowingThis ? 'unfollow' : '') + '" data-user-id="' + user.id + '" data-following="' + (isFollowingThis ? '1' : '0') + '">' +
                (isFollowingThis ? 'Deixar de Seguir' : 'Seguir') + '</button>';
            }
            
            itemHtml += '</div>';
            container.innerHTML += itemHtml;
        });

        // Re-anexar eventos aos novos botões (se houver)
        attachFollowEvents();
    }

    // Função para anexar eventos aos botões de seguir no modal
    function attachFollowEvents() {
        var followButtonsList = document.querySelectorAll('.follow-button-list');
        followButtonsList.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var isFollowing = btn.getAttribute('data-following') === '1';
                var action = isFollowing ? 'unfollow' : 'follow';
                var followedId = btn.getAttribute('data-user-id');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'src/actions/follow_action.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json.success) {
                            btn.setAttribute('data-following', isFollowing ? '0' : '1');
                            btn.classList.toggle('unfollow');
                            btn.textContent = isFollowing ? 'Seguir' : 'Deixar de Seguir';
                            showMessage(json.message, 'success');
                            // Recarregar a aba atual para refletir mudanças
                            var activeTab = document.querySelector('.modal-tab-link.active');
                            if (activeTab) {
                                activateTab(activeTab.getAttribute('data-tab'), currentViewingId);
                            }
                        } else {
                            showMessage(json.message, 'error');
                        }
                    } catch (e) {
                        console.error('Erro ao parsear JSON:', e);
                        showMessage('Erro na resposta', 'error');
                    }
                };
                xhr.onerror = function() {
                    showMessage('Erro de rede', 'error');
                };
                xhr.send('action=' + action + '&followed_id=' + followedId);
            });
        });
    }

    // Abrir o modal ao clicar nas estatísticas
    statLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var tabName = link.getAttribute('data-tab');
            if (tabName === 'posts') return;
            // Assumir viewingId do data-followed-id do followBtn ou de uma variável global (ajuste conforme necessário)
            var viewingId = followBtn ? followBtn.getAttribute('data-followed-id') : null;
            if (!viewingId) viewingId = currentViewingId; // Fallback
            activateTab(tabName, viewingId);
            openModal(viewingId);
        });
    });

    // Trocar abas dentro do modal
    modalTabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            var tabName = tab.getAttribute('data-tab');
            activateTab(tabName, currentViewingId);
        });
    });

    // Fechar o modal
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
    if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);

    // Follow/Unfollow para o botão principal
    if (followBtn) {
        followBtn.addEventListener('click', function() {
            var isFollowing = followBtn.getAttribute('data-following') === '1';
            var action = isFollowing ? 'unfollow' : 'follow';
            var followedId = followBtn.getAttribute('data-followed-id');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'src/actions/follow_action.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json.success) {
                        followBtn.setAttribute('data-following', isFollowing ? '0' : '1');
                        followBtn.classList.toggle('following');
                        followBtn.innerHTML = isFollowing
                            ? '<i class="fas fa-user-plus"></i> Seguir'
                            : '<i class="fas fa-user-minus"></i> Deixar de seguir';

                        if (followersCountEl) followersCountEl.textContent = json.followers_count;
                        if (followingCountEl) followingCountEl.textContent = json.following_count;
                        showMessage(json.message, 'success');
                    } else {
                        showMessage(json.message, 'error');
                    }
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    showMessage('Erro na resposta', 'error');
                }
            };
            xhr.onerror = function() {
                showMessage('Erro de rede', 'error');
            };
            xhr.send('action=' + action + '&followed_id=' + followedId);
        });
    }

    // JS para edição de perfil
    var editBtn = document.getElementById('editBtn');
    var editForm = document.getElementById('editForm');
    var avatarPreview = document.querySelector('.profile-avatar-lg');
    var profileImageInput = document.getElementById('profile_image_input');
    var localFileInput = document.querySelector('input[name="profile_image_file"]');

    if (editBtn && editForm) {
        editBtn.addEventListener('click', function() {
            editForm.style.display = editForm.style.display === 'block' ? 'none' : 'block';
            if (editForm.style.display === 'block') editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        if (localFileInput) {
            localFileInput.addEventListener('change', function(){
                var f = this.files[0];
                if (f) {
                    if (currentObjectURL) URL.revokeObjectURL(currentObjectURL);
                    currentObjectURL = URL.createObjectURL(f);
                    if (avatarPreview) avatarPreview.src = currentObjectURL;
                    if (profileImageInput) profileImageInput.value = '';
                }
            });
        }

        // Cloudinary (remova se causar problemas)
        if (typeof cloudinary !== 'undefined') {
            var cloudName = "dctvku3xp";
            var uploadPreset = "cryptmedia";
            var widget = cloudinary.createUploadWidget({
                cloudName: cloudName, uploadPreset: uploadPreset,
                sources: ["local","url","camera","image_search"],
                multiple: false, maxFileSize: 5 * 1024 * 1024, cropping: false, resourceType: "image"
            }, function(err, result) {
                if (err) { console.error(err); showMessage('Erro no upload','error'); return; }
                if (result && result.event === 'success') {
                    if (profileImageInput) profileImageInput.value = result.info.secure_url;
                    if (avatarPreview) avatarPreview.src = result.info.secure_url;
                    showMessage('Upload concluído', 'success');
                }
            });

            var uploadBtn = document.getElementById('upload_widget_btn');
            if (uploadBtn) {
                uploadBtn.addEventListener('click', function() {
                    if (widget) widget.open();
                    else showMessage('Widget não inicializado','error');
                });
            }
        }

        // Submissão do formulário
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var fd = new FormData(editForm);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'src/actions/profile_update.php', true);
            xhr.onload = function() {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json.success) {
                        showMessage(json.message || 'Salvo', 'success');
                        if (json.avatar_url && avatarPreview) avatarPreview.src = json.avatar_url;
                        if (json.bio) {
                            var bioEl = document.querySelector('.bio');
                            if (bioEl) bioEl.textContent = json.bio;
                        }
                        editForm.style.display = 'none';
                    } else {
                        showMessage(json.message || 'Erro', 'error');
                    }
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    showMessage('Erro na resposta', 'error');
                }
            };
            xhr.onerror = function() {
                showMessage('Erro de rede', 'error');
            };
            xhr.send(fd);
        });
    }

    // Função auxiliar para mensagens
    function showMessage(text, type) {
        if (messageDiv) {
            messageDiv.innerHTML = '<div class="' + (type === 'success' ? 'success-msg' : 'error-msg') + '">' + text + '</div>';
            setTimeout(function() { messageDiv.innerHTML = ''; }, 4000);
        }
    }

    var currentObjectURL = null;
});

document.getElementById("follow-btn")?.addEventListener("click", function() {

    const btn = this;
    const following = btn.getAttribute("data-following");
    const userId = btn.getAttribute("data-user");

    fetch("followers.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=${following == "1" ? "unfollow" : "follow"}&user_id=${userId}`
    })
    .then(res => res.text())
    .then(data => {
        // Reload SOMENTE o botão, sem reload da página toda
        fetch(`profile.php?id=${userId} #follow-container`)
        .then(response => response.text())
        .then(html => {
            document.querySelector("#follow-container").innerHTML = 
                new DOMParser()
                .parseFromString(html, "text/html")
                .querySelector("#follow-container").innerHTML;
        });
    });
});