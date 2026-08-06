const LINK = window.location.origin + '/kits/';

$.ajaxSetup({
    xhrFields: {
        withCredentials: true
    }
});

const dataTableDefaults = {
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    language: {
        emptyTable: "Aucune donn\u00e9e disponible dans le tableau",
        loadingRecords: "Chargement...",
        processing: "Traitement...",
        search: "Rechercher\u00a0:",
        lengthMenu: "Afficher _MENU_ entr\u00e9es",
        zeroRecords: "Aucune entr\u00e9e correspondante trouv\u00e9e",
        info: "Affichage de _START_ \u00e0 _END_ sur _TOTAL_ entr\u00e9es",
        infoEmpty: "Affichage de 0 \u00e0 0 sur 0 entr\u00e9es",
        infoFiltered: "(filtr\u00e9es depuis un total de _MAX_ entr\u00e9es)",
        paginate: { first: "Premi\u00e8re", last: "Derni\u00e8re", next: "Suivante", previous: "Pr\u00e9c\u00e9dente" },
        aria: { sortAscending: " : activer pour trier la colonne par ordre croissant", sortDescending: " : activer pour trier la colonne par ordre d\u00e9croissant" },
        thousands: " ",
        decimal: ","
    },
    processing: true,
    serverSide: false
};

function initDataTable(tableId, ajaxUrl, columns) {
    return $(`#${tableId}`).DataTable({
        ...dataTableDefaults,
        ajax: { url: LINK + ajaxUrl, dataSrc: 'data' },
        columns: columns,
        drawCallback: function() {
            if (window.innerWidth <= 768) {
                const api = $(`#${tableId}`).DataTable();
                const headers = [];
                $(`#${tableId} thead th`).each(function() {
                    headers.push($(this).text().trim());
                });
                $(api.rows().nodes()).each(function() {
                    $(this).find('td').each(function(idx, td) {
                        if (headers[idx]) $(td).attr('data-label', headers[idx]);
                    });
                });
            }
        }
    });
}

function bindStatusToggle(tableId, changerUrl, tableRef) {
    $(`#${tableId}`).on('click', '.changerStatus', function() {
        const id = $(this).data('id');
        $.post(LINK + changerUrl, { id: id }, function(response) {
            console.log(response);
            
            showToast(response.message, response.status === 1 ? 'success' : 'error');
            if (response.status === 1 && response.reload) {
                $(`#${tableId}`).DataTable().ajax.reload(null, false);
            }
        }, 'json');
    });
}

const entityConfigs = {
    product: {
        modalTitle: 'Ajouter un produit',
        fields: [
            { name: 'libelle', label: 'Libellé', type: 'text', required: true, col: 1 },
            { name: 'categorie_code', label: 'Catégorie', type: 'select', required: true, optionsKey: 'PRODUCT_CATEGORIE_OPTIONS', col: 2 },
            { name: 'sous_categorie_code', label: 'Sous-catégorie', type: 'select', required: false, col: 1 },
            { name: 'fournisseur_code', label: 'Fournisseur', type: 'select', required: true, optionsKey: 'PRODUCT_FOURNISSEUR_OPTIONS', col: 2 },
            { name: 'prix_fournisseur', label: 'Prix fournisseur (FCFA)', type: 'number', required: false, col: 1 },
            { name: 'prix_vente', label: 'Prix vente (FCFA)', type: 'number', required: true, col: 2 },
            { name: 'stock', label: 'Stock initial', type: 'number', required: false, col: 1 },
            { name: 'description', label: 'Description', type: 'textarea', required: false, col: 'full' },
            { name: 'images', label: 'Images du produit', type: 'image-upload', required: false, col: 'full' }
        ]
    },
    category: {
        modalTitle: 'Ajouter une catégorie',
        fields: [
            { name: 'libelle', label: 'Libellé', type: 'text', required: true },
            { name: 'description', label: 'Description', type: 'textarea', required: false }
        ]
    },
    client: {
        modalTitle: 'Ajouter un client',
        fields: [
            { name: 'nom', label: 'Nom', type: 'text', required: true },
            { name: 'telephone', label: 'Téléphone', type: 'tel', required: true },
            { name: 'email_client', label: 'Email', type: 'email', required: false },
            { name: 'quartier_client', label: 'Quartier', type: 'text', required: false },
            { name: 'adresse_client', label: 'Adresse', type: 'textarea', required: false }
        ]
    },
    utilisateur: {
        modalTitle: 'Ajouter un utilisateur',
        fields: [
            { name: 'nom', label: 'Nom', type: 'text', required: true },
            { name: 'prenom', label: 'Prénom', type: 'text', required: false },
            { name: 'email', label: 'Email', type: 'email', required: false },
            { name: 'telephone', label: 'Téléphone', type: 'tel', required: true },
            { name: 'password', label: 'Mot de passe', type: 'password', required: false },
            { name: 'role_code', label: 'Rôle', type: 'select', required: true, options: { 'ROLE-ADMIN': 'Administrateur', 'ROLE-PRO': 'Propriétaire', 'ROLE-LIV': 'Livreur' } },
            { name: 'actif', label: 'Statut', type: 'select', required: false, options: { 'actif': 'Actif', 'inactif': 'Inactif' } }
        ]
    },
    order: {
        modalTitle: 'Nouvelle commande',
        fields: [
            { name: 'client_code', label: 'Code client', type: 'text', required: true },
            { name: 'adresse_livraison_commande', label: 'Adresse', type: 'textarea', required: true },
            { name: 'montant_total_commande', label: 'Montant', type: 'number', required: true },
            { name: 'statut_commande', label: 'Statut', type: 'select', required: false, options: { 'en_attente': 'En attente', 'confirmee': 'Confirmée', 'en_livraison': 'En livraison', 'livree': 'Livrée', 'annulee': 'Annulée' } },
            { name: 'methode_paiement_commande', label: 'Paiement', type: 'select', required: false, options: { 'cash': 'Espèces', 'mobile_money': 'Mobile Money', 'carte': 'Carte' } }
        ]
    },
    fournisseur: {
        modalTitle: 'Ajouter un fournisseur',
        fields: [
            { name: 'nom', label: 'Nom', type: 'text', required: true },
            { name: 'telephone', label: 'Téléphone', type: 'text', required: true },
            { name: 'whatsapp', label: 'WhatsApp', type: 'text', required: false },
            { name: 'localisation', label: 'Localisation', type: 'text', required: false },
            { name: 'mode_collaboration', label: 'Mode collaboration', type: 'select', required: true, options: { 'achat_direct': 'Achat direct', 'dropshipping': 'Dropshipping', 'depot_vente': 'Dépôt vente', 'commission': 'Commission' } }
        ]
    },
    livreur: {
        modalTitle: 'Ajouter un livreur',
        fields: [
            { name: 'nom', label: 'Nom complet', type: 'text', required: true },
            { name: 'telephone', label: 'Téléphone', type: 'text', required: true },
            { name: 'email', label: 'Email', type: 'email', required: false },
            { name: 'moyen_transport', label: 'Moyen de transport', type: 'select', required: true, options: { 'moto': 'Moto', 'voiture': 'Voiture', 'velo': 'Vélo', 'tricycle': 'Tricycle' } }
        ]
    },
    paiement: {
        modalTitle: 'Ajouter un paiement',
        fields: [
            { name: 'commande_code', label: 'Code commande', type: 'text', required: true },
            { name: 'montant_paiement', label: 'Montant (FCFA)', type: 'number', required: true },
            { name: 'methode_paiement', label: 'Méthode', type: 'select', required: false, options: { 'cash': 'Espèces', 'mobile_money': 'Mobile Money', 'carte': 'Carte' } },
            { name: 'reference_transaction', label: 'Référence', type: 'text', required: false },
            { name: 'statut_paiement', label: 'Statut', type: 'select', required: false, options: { 'en_attente': 'En attente', 'partiel': 'Partiel', 'paye': 'Payé', 'echoue': 'Échoué' } }
        ]
    },
    role: {
        modalTitle: 'Ajouter un rôle',
        fields: [
            { name: 'nom', label: 'Nom du rôle', type: 'text', required: true },
            { name: 'description', label: 'Description', type: 'textarea', required: false }
        ]
    },
    permission: {
        modalTitle: 'Ajouter une permission',
        fields: [
            { name: 'module', label: 'Module', type: 'text', required: true },
            { name: 'action', label: 'Action', type: 'text', required: true },
            { name: 'description', label: 'Description', type: 'textarea', required: false }
        ]
    },
    stock: {
        modalTitle: 'Nouveau mouvement',
        fields: [
            { name: 'produit_code', label: 'Code produit', type: 'text', required: true },
            { name: 'type_mouvement', label: 'Type', type: 'select', required: true, options: { 'entree': 'Entrée', 'sortie': 'Sortie' } },
            { name: 'quantite', label: 'Quantité', type: 'number', required: true },
            { name: 'source', label: 'Source', type: 'text', required: false }
        ]
    },
    commission: {
        modalTitle: 'Ajouter une commission',
        fields: [
            { name: 'commande_code', label: 'Code commande', type: 'text', required: true },
            { name: 'fournisseur_code', label: 'Code fournisseur', type: 'text', required: true },
            { name: 'montant_commission', label: 'Montant (FCFA)', type: 'number', required: true },
            { name: 'statut_commission', label: 'Statut', type: 'select', required: false, options: { 'en_attente': 'En attente', 'payee': 'Payée', 'annulee': 'Annulée' } }
        ]
    },
    setting: {
        modalTitle: 'Ajouter un paramètre',
        fields: [
            { name: 'libelle', label: 'Libellé', type: 'text', required: true },
            { name: 'valeur', label: 'Valeur', type: 'text', required: true },
            { name: 'type_parametre', label: 'Type', type: 'select', required: false, options: { 'texte': 'Texte', 'nombre': 'Nombre', 'bool': 'Booléen' } },
            { name: 'statut_parametre', label: 'Statut', type: 'select', required: false, options: { 'actif': 'Actif', 'inactif': 'Inactif' } }
        ]
    },
    city: {
        modalTitle: 'Ajouter une ville',
        fields: [
            { name: 'code', label: 'Code', type: 'text', required: true },
            { name: 'libelle', label: 'Libellé', type: 'text', required: true },
            { name: 'pays_code', label: 'Pays', type: 'text', required: false }
        ]
    },
    kit: {
        modalTitle: 'Ajouter un kit',
        fields: [
            { name: 'code', label: 'Code', type: 'text', required: true },
            { name: 'libelle', label: 'Libellé', type: 'text', required: true },
            { name: 'prix', label: 'Prix (FCFA)', type: 'number', required: true },
            { name: 'description_kit', label: 'Description', type: 'textarea', required: false }
        ]
    },
    subcategory: {
        modalTitle: 'Ajouter une sous-catégorie',
        fields: [
            { name: 'libelle', label: 'Libellé', type: 'text', required: true },
            { name: 'categorie_code', label: 'Catégorie', type: 'select', required: true, optionsKey: 'SUBCATEGORY_CATEGORY_OPTIONS' },
            { name: 'description', label: 'Description', type: 'textarea', required: false }
        ]
    }
};

const entityOptionEndpoints = {
    SUBCATEGORY_CATEGORY_OPTIONS: LINK + 'category/getActive',
    PRODUCT_CATEGORIE_OPTIONS: LINK + 'category/getActive',
    PRODUCT_SOUSCATEGORIE_OPTIONS: LINK + 'subcategory/getActive',
    PRODUCT_FOURNISSEUR_OPTIONS: LINK + 'fournisseur/getActive'
};

function getEntityConfig(type) {
    let config = entityConfigs[type] || { modalTitle: 'Ajouter', fields: [] };
    let resolved = JSON.parse(JSON.stringify(config));
    resolved.type = type;
    resolved.fields.forEach(function(f) {
        if (f.optionsKey) {
        const opts = window[f.optionsKey];
            if (opts && typeof opts === 'object' && Object.keys(opts).length > 0) {
                f.options = opts;
            } else if (entityOptionEndpoints[f.optionsKey]) {
                f._optionsEndpoint = entityOptionEndpoints[f.optionsKey];
                f._optionsKey = f.optionsKey;
                f.options = { '': 'Chargement...' };
            } else {
                f.options = {};
            }
            delete f.optionsKey;
        }
    });
    return resolved;
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('genericModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalForm = document.getElementById('modalForm');
    const modalSave = document.getElementById('modalSave');
    let currentFormConfig = null;
    let currentSaveHandler = null;
    let currentFormType = null;

    window.openModal = function(type, saveHandler) {
        if (!modal) return;
        currentFormConfig = getEntityConfig(type);
        currentFormType = type;
        const formType = type;
        currentSaveHandler = saveHandler || function(formData) {
            const endpoints = {
                product: LINK + 'product/add',
                category: LINK + 'category/add',
                client: LINK + 'clientController/add',
                utilisateur: LINK + 'user/add',
                order: LINK + 'order/add',
                fournisseur: LINK + 'fournisseur/add',
                livreur: LINK + 'livreur/add',
                paiement: LINK + 'paiement/add',
                role: LINK + 'role/add',
                permission: LINK + 'permission/add',
                stock: LINK + 'stock/add',
                commission: LINK + 'commission/add',
                setting: LINK + 'setting/add',
                city: LINK + 'city/add',
                subcategory: LINK + 'subcategory/add',
                kit: LINK + 'kit/add'
            };
            const url = endpoints[type];

            if (!url) {
                showToast('Type de formulaire inconnu: ' + type, 'error');
                return;
            }

            loading(modalSave, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    loading(modalSave, false, 'Enregistrer');
                    if (response.status) {
                        showToast(response.message, 'success');
                        closeModal();
                        const table = $('#dataTable').DataTable();
                        if (table) table.ajax.reload(null, false);
                        console.log('[form success] type=', formType, 'response=', response);
                        if (formType === 'kit' && response.kit_code) {
                            setTimeout(function() {
                                window.openCompositionPanel(response.kit_code);
                            }, 400);
                        }
                        if (formType === 'client' && response.client_code) {
                            setTimeout(function() {
                                window.openCommandeModal(response.client_code);
                            }, 400);
                        }
                    } else {
                        showToast(response.message || 'Erreur lors de l\'enregistrement', 'error');
                    }
                },
                error: function(xhr) {
                    loading(modalSave, false, 'Enregistrer');
                    let errorMsg = 'Erreur serveur';
                    console.error('[form error] status:', xhr.status, 'response:', xhr.responseText);
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 401) {
                        errorMsg = 'Authentification requise - Veuillez vous reconnecter';
                    } else if (xhr.status === 419) {
                        errorMsg = 'Token de sécurité invalide - Actualisez la page';
                    } else if (xhr.responseText) {
                        errorMsg = 'Erreur ' + xhr.status + ': ' + xhr.responseText.slice(0, 150);
                    }
                    showToast(errorMsg, 'error');
                }
            });
        };
        modalTitle.textContent = currentFormConfig.modalTitle;
        const isProductModal = type === 'product';
        const isLargeModal = isProductModal;
        if (isLargeModal && window.innerWidth > 768) {
            modal.querySelector('.modal').style.maxWidth = '900px';
            modal.querySelector('.modal').style.width = '96%';
        }
        let html = '<input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">';
        const fields = currentFormConfig.fields;
        const rows = [];
        const forceSingleCol = fields.length <= 5;
        let row = [];
        fields.forEach(function(f) {
            f.options = f.options || {};
            if (forceSingleCol) {
                if (row.length) rows.push(row);
                rows.push([f]);
                row = [];
            } else if (f.col === 'full') {
                if (row.length) rows.push(row);
                rows.push([f]);
                row = [];
            } else if (f.col === 2 && row.length === 1) {
                row.push(f);
                rows.push(row);
                row = [];
            } else {
                if (row.length === 2) {
                    rows.push(row);
                    row = [];
                }
                row.push(f);
            }
        });
        if (row.length) rows.push(row);

        rows.forEach(function(row) {
            const isFull = row.length === 1;
            html += '<div class="' + (isFull ? 'form-row-full' : 'form-row') + '">';
            row.forEach(function(f) {
                const colClass = isFull ? 'form-group-full' : ('form-group-' + (f.col === 2 ? 2 : 1));
                html += '<div class="form-group ' + colClass + '"><label>' + f.label + (f.required ? ' *' : '') + '</label><div class="input-wrapper">';
                if (f.type === 'select') {
                    html += '<select name="' + f.name + '" ' + (f.required ? 'required' : '') + '>' + Object.entries(f.options || {}).map(function(e) { return '<option value="' + e[0] + '">' + e[1] + '</option>'; }).join('') + '</select>';
                } else if (f.type === 'textarea') {
                    html += '<textarea name="' + f.name + '" ' + (f.required ? 'required' : '') + '></textarea>';
                } else if (f.type === 'image-upload') {
                    html += '<input type="file" name="image_files" multiple accept="image/*" class="image-upload-input" style="display:none">';
                    html += '<button type="button" class="btn btn-sm btn-outline-primary image-upload-trigger"><i class="fa fa-cloud-upload"></i> Ajouter des images</button>';
                    html += '<div class="image-previews-grid"></div>';
                } else {
                    html += '<input type="' + f.type + '" name="' + f.name + '" ' + (f.required ? 'required' : '') + '>';
                }
                html += '<span class="field-feedback-icon" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></span></div></div>';
            });
            html += '</div>';
        });
        modalForm.innerHTML = html;
        modalForm.dataset.entity = type;
        modal.classList.add('active');
        modalForm._uploadedImages = [];

        if (isProductModal) {
            const fileInput = modalForm.querySelector('.image-upload-input');
            const triggerBtn = modalForm.querySelector('.image-upload-trigger');
            const previewsGrid = modalForm.querySelector('.image-previews-grid');

            triggerBtn.addEventListener('click', function() { fileInput.click(); });

            fileInput.addEventListener('change', function() {
                Array.from(this.files).forEach(function(file) {
                    uploadImageFile(file, previewsGrid, modalForm);
                });
                this.value = '';
            });

            function uploadImageFile(file, grid, form) {
                const fd = new FormData();
                fd.append('image', file);
                fd.append('csrf_token', form.querySelector('[name="csrf_token"]').value);
                $.ajax({
                    url: LINK + 'product/uploadImage',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        if (resp.status) {
                            const entry = { filename: resp.filename, url: LINK + resp.url, is_primary: false };
                            form._uploadedImages.push(entry);
                            renderPreview(entry, grid, form);
                        } else {
                            showToast(resp.message || 'Erreur upload', 'error');
                        }
                    },
                    error: function() { showToast('Erreur upload image', 'error'); }
                });
            }

            function renderPreview(entry, grid, form) {
                const card = document.createElement('div');
                card.className = 'image-preview-card';
                card.innerHTML = `
                    <img src="${entry.url}" alt="preview">
                    <div class="image-preview-actions">
                        <label class="principal-check">
                            <input type="radio" name="img_principal" value="${entry.filename}" ${entry.is_primary ? 'checked' : ''}> Principal
                        </label>
                        <button type="button" class="image-remove-btn" title="Supprimer">&times;</button>
                    </div>
                `;
                const radio = card.querySelector('input[type="radio"]');
                radio.addEventListener('change', function() {
                    form._uploadedImages.forEach(function(img) { img.is_primary = (img.filename === entry.filename); });
                });
                card.querySelector('.image-remove-btn').addEventListener('click', function() {
                    form._uploadedImages = form._uploadedImages.filter(function(img) { return img.filename !== entry.filename; });
                    card.remove();
                });
                grid.appendChild(card);
            }
        }

        currentFormConfig.fields.forEach(function(f) {
            if (f._optionsEndpoint) {
                const select = modalForm.querySelector('select[name="' + f.name + '"]');
                if (!select) return;
                $.get(f._optionsEndpoint, function(resp) {
                    const options = (resp && resp.options) ? resp.options : {};
                    const entries = Object.entries(options);
                    const placeholder = entries.find(function(e) { return e[0] === ''; });
                    const realOptions = entries.filter(function(e) { return e[0] !== ''; });
                    const placeholderHtml = placeholder
                        ? '<option value="" disabled selected>' + placeholder[1] + '</option>'
                        : '<option value="" disabled selected>Sélectionner...</option>';
                    select.innerHTML = placeholderHtml + realOptions.map(function(e) {
                        return '<option value="' + e[0] + '">' + e[1] + '</option>';
                    }).join('');
                }, 'json').fail(function(xhr) {
                    console.error('[openModal] Options load failed:', f._optionsEndpoint, 'status:', xhr.status, 'response:', xhr.responseJSON);
                    select.innerHTML = '<option value="">Erreur chargement</option>';
                });
            }
        });

        if (isProductModal) {
            const categorieSelect = modalForm.querySelector('select[name="categorie_code"]');
            const sousCategorieSelect = modalForm.querySelector('select[name="sous_categorie_code"]');
            console.log('[product modal] categorieSelect:', !!categorieSelect, 'sousCategorieSelect:', !!sousCategorieSelect);
            if (categorieSelect && sousCategorieSelect) {
                console.log('[product modal] attaching change listener');
                categorieSelect.addEventListener('change', function() {
                    console.log('[product modal] categorie changed to:', this.value);
                    const catCode = this.value;
                    if (!catCode) {
                        sousCategorieSelect.innerHTML = '<option value="" disabled selected>Sélectionner une sous-catégorie</option>';
                        sousCategorieSelect.disabled = true;
                        return;
                    }
                    sousCategorieSelect.disabled = true;
                    sousCategorieSelect.innerHTML = '<option value="">Chargement...</option>';
                    $.get(LINK + 'subcategory/getByCategory', { categorie_code: catCode }, function(resp) {
                        const options = resp.options || {};
                        const entries = Object.entries(options);
                        const placeholder = entries.find(function(e) { return e[0] === ''; });
                        const realOptions = entries.filter(function(e) { return e[0] !== ''; });
                        const placeholderHtml = placeholder
                            ? '<option value="" disabled selected>' + placeholder[1] + '</option>'
                            : '<option value="" disabled selected>Sélectionner...</option>';
                        sousCategorieSelect.innerHTML = placeholderHtml + realOptions.map(function(e) {
                            return '<option value="' + e[0] + '">' + e[1] + '</option>';
                        }).join('');
                        sousCategorieSelect.disabled = false;
                    }, 'json').fail(function() {
                        sousCategorieSelect.innerHTML = '<option value="">Erreur chargement</option>';
                        sousCategorieSelect.disabled = false;
                    });
                });
            }
        }
    };

    window.closeModal = function() {
        if (modal) {
            modal.classList.remove('active');
            if (modalForm) modalForm.reset();
        }
        currentFormConfig = null;
        currentSaveHandler = null;
        currentFormType = null;
    };

    window.openCommandeModal = function(clientCode) {
        console.log('[openCommandeModal] appelé avec clientCode=', clientCode);
        const overlay = document.getElementById('commandeModalOverlay');
        const modal = overlay ? overlay.querySelector('.modal') : null;
        console.log('[openCommandeModal] overlay=', !!overlay, 'modal=', !!modal);

        if (!overlay || !modal) {
            console.warn('[openCommandeModal] modal commande introuvable => redirection vers client/list');
            window.location.href = LINK + 'client/list?new_client=' + encodeURIComponent(clientCode || '');
            return;
        }

        const clientCodeInput = document.getElementById('cmdClientCode');
        const clientCodeDisplay = document.getElementById('cmdClientCodeDisplay');
        const campagneDisplay = document.getElementById('cmdCampagneDisplay');
        const kitSelect = document.getElementById('cmdKitSelect');
        const addKitBtn = document.getElementById('cmdAddKitBtn');
        const selectedKitsContainer = document.getElementById('cmdSelectedKits');
        const saveBtn = document.getElementById('commandeModalSave');
        const cancelBtn = document.getElementById('commandeModalCancel');
        const closeBtn = document.getElementById('commandeModalClose');
        console.log('[openCommandeModal] éléments:', !!clientCodeInput, !!clientCodeDisplay, !!campagneDisplay, !!kitSelect, !!addKitBtn, !!selectedKitsContainer, !!saveBtn, !!cancelBtn, !!closeBtn);

        if (!clientCodeInput || !clientCodeDisplay || !campagneDisplay || !kitSelect || !addKitBtn || !selectedKitsContainer || !saveBtn || !cancelBtn || !closeBtn) {
            console.warn('[openCommandeModal] élément manquant => redirection vers client/list');
            window.location.href = LINK + 'client/list?new_client=' + encodeURIComponent(clientCode || '');
            return;
        }

        let selectedKits = [];
        let currentCampagneCode = '';

        function renderSelectedKits() {
            selectedKitsContainer.innerHTML = selectedKits.map(function(kit, idx) {
                var articlesHtml = '';
                if (kit.articles && kit.articles.length > 0) {
                    articlesHtml = '<div style="margin-top:6px;padding-top:6px;border-top:1px dashed var(--border-color);display:flex;flex-wrap:wrap;gap:6px;">' +
                        kit.articles.map(function(art) {
                            var imgHtml = art.image ? '<img src="' + LINK + 'public/assets/images/articles/' + art.image + '" style="height:24px;width:24px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:4px;" onerror="this.style.display=\'none\'">' : '';
                            return '<span style="font-size:0.85rem;color:#666;display:inline-flex;align-items:center;">' + imgHtml + art.libelle + ' x' + art.quantite + '</span>';
                        }).join('') +
                    '</div>';
                }
                return '<div style="padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius-md);">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<span><strong>' + kit.code + '</strong> - ' + kit.label + '</span>' +
                        '<div style="display:flex;align-items:center;gap:10px;">' +
                            '<span style="font-size:0.85rem;color:#666;">' + (kit.prix ? Number(kit.prix).toLocaleString('fr-FR') + ' FCFA' : '') + '</span>' +
                            '<button type="button" data-idx="' + idx + '" class="btn-remove-kit" style="color:#dc3545;background:none;border:none;cursor:pointer;font-size:1.1rem;">&times;</button>' +
                        '</div>' +
                    '</div>' +
                    articlesHtml +
                '</div>';
            }).join('');
        }

        selectedKitsContainer.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-kit');
            if (!btn) return;
            selectedKits.splice(parseInt(btn.dataset.idx), 1);
            renderSelectedKits();
        });

        addKitBtn.addEventListener('click', function() {
            const code = kitSelect.value;
            const label = kitSelect.options[kitSelect.selectedIndex]?.text || '';
            const prix = kitSelect.options[kitSelect.selectedIndex]?.dataset.prix || '';
            const image = kitSelect.options[kitSelect.selectedIndex]?.dataset.image || '';
            console.log('[cmdAddKitBtn] kit sélectionné:', code, label, prix, image);
            if (!code) return;
            if (selectedKits.find(function(k) { return k.code === code; })) {
                showToast('Ce kit est déjà ajouté', 'warning');
                return;
            }
            const newKit = { code: code, label: label, prix: prix, image: image, articles: [] };
            selectedKits.push(newKit);
            renderSelectedKits();
            kitSelect.value = '';

            $.get(LINK + 'kit/compositionData/' + encodeURIComponent(code), function(resp) {
                console.log('[kit/compositionData]', code, resp);
                if (resp.status && resp.compositions) {
                    const kit = selectedKits.find(function(k) { return k.code === code; });
                    if (kit) {
                        kit.articles = resp.compositions.map(function(c) {
                            return {
                                code: c.article_code,
                                libelle: c.article_libelle,
                                image: c.article_image || '',
                                quantite: c.quantite
                            };
                        });
                        renderSelectedKits();
                    }
                }
            }, 'json').fail(function(xhr) {
                console.error('[kit/compositionData] erreur pour', code, xhr.status, xhr.responseText);
            });
        });

        function closeCommandeModal() {
            overlay.classList.remove('active');
            selectedKits = [];
            selectedKitsContainer.innerHTML = '';
        }

        cancelBtn.addEventListener('click', closeCommandeModal);
        closeBtn.addEventListener('click', closeCommandeModal);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeCommandeModal();
        });

        saveBtn.addEventListener('click', function() {
            const clientCodeVal = clientCodeInput.value.trim();

            if (!clientCodeVal) {
                showToast('Veuillez renseigner le client', 'warning');
                return;
            }
            if (selectedKits.length === 0) {
                showToast('Veuillez ajouter au moins un kit', 'warning');
                return;
            }

            const formData = {
                csrf_token: document.querySelector('#commandeForm [name="csrf_token"]').value,
                client_code: clientCodeVal,
                kits: JSON.stringify(selectedKits.map(function(k) { return { code: k.code }; }))
            };

            loading(saveBtn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');
            $.post(LINK + 'commande/add', formData, function(response) {
                loading(saveBtn, false, 'Enregistrer');
                if (response.status) {
                    showToast(response.message || 'Commande créée avec succès', 'success');
                    closeCommandeModal();
                    const table = $('#dataTable').DataTable();
                    if (table) table.ajax.reload(null, false);
                } else {
                    showToast(response.message || 'Erreur lors de la création', 'error');
                }
            }, 'json').fail(function() {
                loading(saveBtn, false, 'Enregistrer');
                showToast('Erreur serveur', 'error');
            });
        });

        clientCodeInput.value = clientCode || '';
        clientCodeDisplay.value = clientCode || '';
        selectedKitsContainer.innerHTML = '';
        kitSelect.innerHTML = '<option value="">Sélectionner un kit</option>';
        selectedKits = [];
        overlay.classList.add('active');
        console.log('[openCommandeModal] overlay.classList.add(active) => classes=', overlay.className);

        $.get(LINK + 'campagne/getCurrent', function(resp) {
            console.log('[campagne/getCurrent] response:', resp);
            if (resp.status && resp.data) {
                currentCampagneCode = resp.data.code_campagne || '';
                campagneDisplay.value = resp.data.libelle_campagne || '';
            } else {
                campagneDisplay.value = 'Aucune campagne active';
            }
        }, 'json');

        $.get(LINK + 'kit/getActiveByCampagne', { _t: Date.now() }, function(resp) {
            console.log('[kit/getActiveByCampagne] response:', resp);
            const options = resp.options || {};
            const entries = Object.entries(options);
            console.log('[kit/getActiveByCampagne] entries count:', entries.length, entries);
            const placeholder = entries.find(function(e) { return e[0] === ''; });
            const realOptions = entries.filter(function(e) { return e[0] !== ''; });
            const placeholderText = placeholder ? (typeof placeholder[1] === 'string' ? placeholder[1] : (placeholder[1] && placeholder[1].libelle) || 'Sélectionner un kit') : 'Sélectionner un kit';
            const placeholderHtml = '<option value="">' + placeholderText + '</option>';
            const optionsHtml = realOptions.map(function(e) {
                const raw = e[1];
                const opt = typeof raw === 'string' ? { libelle: raw, prix: 0, image: '' } : raw;
                const libelle = (opt && opt.libelle) ? opt.libelle : e[0];
                const prix = (opt && opt.prix) ? opt.prix : 0;
                const image = (opt && opt.image) ? opt.image : '';
                console.log('[kit/getActiveByCampagne] option:', e[0], {libelle, prix, image});
                return '<option value="' + e[0] + '" data-prix="' + prix + '" data-image="' + image + '">' + libelle + '</option>';
            }).join('');
            kitSelect.innerHTML = placeholderHtml + optionsHtml;
            console.log('[kit/getActiveByCampagne] final innerHTML:', kitSelect.innerHTML);
            console.log('[kit/getActiveByCampagne] option elements count:', kitSelect.options.length);
            if (kitSelect.options.length <= 1) {
                console.warn('[kit/getActiveByCampagne] seul le placeholder est présent, chargement des kits généraux...');
                $.get(LINK + 'kit/getActive', function(resp2) {
                    console.log('[kit/getActive] fallback response:', resp2);
                    const options2 = resp2.options || {};
                    const entries2 = Object.entries(options2);
                    const placeholder2 = entries2.find(function(e) { return e[0] === ''; });
                    const realOptions2 = entries2.filter(function(e) { return e[0] !== ''; });
                    const placeholderText2 = placeholder2 ? (typeof placeholder2[1] === 'string' ? placeholder2[1] : 'Sélectionner un kit') : 'Sélectionner un kit';
                    kitSelect.innerHTML = '<option value="">' + placeholderText2 + '</option>' + realOptions2.map(function(e) {
                        const libelle = typeof e[1] === 'string' ? e[1] : (e[1] && e[1].libelle) || e[0];
                        return '<option value="' + e[0] + '">' + libelle + '</option>';
                    }).join('');
                    console.log('[kit/getActive] fallback option elements count:', kitSelect.options.length);
                }, 'json');
            }
        }, 'json');
    };

    if (document.getElementById('modalClose')) {
        document.getElementById('modalClose').addEventListener('click', closeModal);
    }
    if (document.getElementById('modalCancel')) {
        document.getElementById('modalCancel').addEventListener('click', closeModal);
    }
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

if (modalSave) {
        modalSave.addEventListener('click', function() {
            if (!currentSaveHandler || !currentFormConfig) return;
            const form = document.getElementById('modalForm');
            const entityType = Object.keys(entityConfigs).find(key => entityConfigs[key] === currentFormConfig) || 'modal';
            const csrfTokenInput = form.querySelector('[name="csrf_token"]');
            const formData = {};
            
            currentFormConfig.fields.forEach(function(f) {
                const input = form.querySelector(`[name="${f.name}"]`);
                if (f.type === 'image-upload') {
                    formData[f.name] = JSON.stringify(form._uploadedImages || []);
                    return;
                }
                const value = input?.value || '';
                if (f.required || value.trim() !== '') {
                    formData[f.name] = value;
                }
            });
            
            if (csrfTokenInput) {
                formData.csrf_token = csrfTokenInput.value;
            }

            let isFormValid = true;
            if (typeof FormValidator !== 'undefined' && form.dataset.entity) {
                isFormValid = FormValidator.validateForm(form);
            } else {
                const requiredFields = currentFormConfig.fields.filter(f => f.required);
                requiredFields.forEach(function(f) {
                    const input = form.querySelector(`[name="${f.name}"]`);
                    if (input && !input.value.trim()) {
                        isFormValid = false;
                        input.style.borderColor = '#dc3545';
                        const wrapper = input.closest('.input-wrapper');
                        if (wrapper) {
                            let feedbackIcon = wrapper.querySelector('.field-feedback-icon');
                            if (feedbackIcon) {
                                feedbackIcon.classList.add('show');
                                feedbackIcon.innerHTML = FormValidator?.icons.error || '<i class="fa fa-exclamation-circle" style="color: #dc3545;"></i>';
                            }
                        }
                    }
                });
            }

            if (!isFormValid) {
                showToast('Veuillez corriger les erreurs dans le formulaire', 'warning');
                return;
            }
            
            currentSaveHandler(formData);
        });
    }

    document.querySelectorAll('[data-modal]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.getAttribute('data-modal');
            if (type) openModal(type);
        });
    });

    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const footer = document.getElementById('footer');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent?.classList.toggle('expanded');
            footer?.classList.toggle('expanded');
        });
    }

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (mobileMenuBtn && sidebar) {
        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            backdrop.id = 'sidebarBackdrop';
            document.body.appendChild(backdrop);
            document.body.style.overflow = 'hidden';
            backdrop.addEventListener('click', closeMobileSidebar);
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (backdrop) backdrop.remove();
            document.body.style.overflow = '';
        }

        mobileMenuBtn.addEventListener('click', function() {
            if (sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            }
        });
    }

    const searchToggle = document.getElementById('searchToggle');
    const searchMobile = document.getElementById('searchMobile');
    if (searchToggle && searchMobile) {
        searchToggle.addEventListener('click', function() {
            const isActive = searchMobile.classList.contains('active');
            document.querySelectorAll('.search-wrapper--mobile').forEach(function(el) { el.classList.remove('active'); });
            if (!isActive) {
                searchMobile.classList.add('active');
                const input = searchMobile.querySelector('#globalSearchMobile');
                if (input) setTimeout(function() { input.focus(); }, 100);
            }
        });
    }

    document.addEventListener('click', function(e) {
        let clickedInsideOpenDropdown = false;
        document.querySelectorAll('.dropdown-panel.active').forEach(function(p) {
            if (p.contains(e.target)) {
                clickedInsideOpenDropdown = true;
            }
        });
        if (!clickedInsideOpenDropdown) {
            document.querySelectorAll('.dropdown-panel').forEach(function(p) {
                p.classList.remove('active');
            });
        }
    });

    // Campaign selector
    const campaignBtn = document.getElementById('campaignBtn');
    const campaignPanel = document.getElementById('campaignPanel');
    const campaignList = document.getElementById('campaignList');

    function loadCampaigns() {
        if (!campaignList) return;
        $.get(LINK + 'campagne/getActive', function(resp) {
            const options = resp.options || {};
            const currentCampagne = window.CAMPAIGN_ACTIVE || null;
            let html = '';
            Object.entries(options).forEach(function(e) {
                if (e[0] === '') return;
                const isActive = currentCampagne && currentCampagne.code_campagne === e[0];
                html += '<a href="#" class="campaign-item' + (isActive ? ' active' : '') + '" data-code="' + e[0] + '">' +
                    '<span class="campaign-name">' + e[1] + '</span>' +
                    (isActive ? '<span class="campaign-badge">Actuelle</span>' : '') +
                    '</a>';
            });
            if (!html) {
                html = '<div class="dropdown-empty">Aucune campagne active</div>';
            }
            campaignList.innerHTML = html;
        }, 'json').fail(function() {
            if (campaignList) {
                campaignList.innerHTML = '<div class="dropdown-empty">Erreur chargement campagnes</div>';
            }
        });
    }

    if (campaignBtn && campaignPanel) {
        campaignBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.dropdown-panel').forEach(function(p) { p.classList.remove('active'); });
            campaignPanel.classList.toggle('active');
            if (campaignPanel.classList.contains('active')) {
                loadCampaigns();
            }
        });
        if (campaignList) {
            campaignList.addEventListener('click', function(e) {
                e.preventDefault();
                const item = e.target.closest('.campaign-item');
                if (!item) return;
                const code = item.dataset.code;
                $.post(LINK + 'campagne/setActive', { code: code, csrf_token: document.getElementById('csrf_token')?.value || '' }, function(resp) {
                    if (resp.status) {
                        window.CAMPAIGN_ACTIVE = resp.active;
                        showToast('Campagne activée', 'success');
                        campaignPanel.classList.remove('active');
                        loadCampaigns();
                    } else {
                        showToast(resp.message || 'Erreur', 'error');
                    }
                }, 'json').fail(function() {
                    showToast('Erreur serveur', 'error');
                });
            });
        }
    }

    ['notification', 'profile', 'quickActions'].forEach(function(name) {
        const btn = document.getElementById(name + 'Btn');
        const panel = document.getElementById(name + 'Panel');
        if (btn && panel) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.querySelectorAll('.dropdown-panel').forEach(function(p) { p.classList.remove('active'); });
                panel.classList.toggle('active');
            });
        }
    });

    ['bnProfil'].forEach(function(id) {
        const btn = document.getElementById(id);
        const panel = document.getElementById('panelProfil');
        if (btn && panel) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.querySelectorAll('.dropdown-panel').forEach(function(p) { p.classList.remove('active'); });
                panel.classList.toggle('active');
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-panel') && !e.target.closest('.bottom-nav-item')) {
            document.querySelectorAll('.dropdown-panel').forEach(function(p) { p.classList.remove('active'); });
        }
    });

    function updateBottomNavActive() {
        const path = window.location.pathname.replace(/^\/kits\/?/, '/');
        document.querySelectorAll('.bottom-nav-item').forEach(function(item) {
            item.classList.remove('active');
            const href = item.getAttribute('href') || '';
            const cleanHref = href.replace(/^\/kits\/?/, '/');
            if (path === cleanHref || path.startsWith(cleanHref + '/')) {
                item.classList.add('active');
            }
        });
    }

    updateBottomNavActive();

    window.openCompositionPanel = function(kitCode) {
        const panel = document.getElementById('compositionPanel');
        const articleSelect = document.getElementById('compositionArticle');
        const quantiteInput = document.getElementById('compositionQuantite');
        const list = document.getElementById('compositionList');
        if (!panel) return;

        panel.dataset.kitCode = kitCode;
        panel.classList.add('active');
        document.body.style.overflow = 'hidden';
        list.innerHTML = '<div class="composition-loading">Chargement...</div>';
        articleSelect.innerHTML = '<option value="">Sélectionner un article</option>';
        quantiteInput.value = 1;

        $.get(LINK + 'kit/compositionData/' + kitCode, function(resp) {
            if (!resp || resp.status !== 1) {
                list.innerHTML = '<div class="composition-empty">Erreur lors du chargement</div>';
                return;
            }
            const options = resp.articleOptions || {};
            const entries = Object.entries(options);
            const placeholder = entries.find(function(e) { return e[0] === ''; });
            const realOptions = entries.filter(function(e) { return e[0] !== ''; });
            const placeholderHtml = placeholder
                ? '<option value="" disabled selected>' + placeholder[1] + '</option>'
                : '<option value="" disabled selected>Sélectionner un article</option>';
            articleSelect.innerHTML = placeholderHtml + realOptions.map(function(e) {
                return '<option value="' + e[0] + '">' + e[1] + '</option>';
            }).join('');

            renderCompositionList(resp.compositions || []);
        }, 'json').fail(function() {
            list.innerHTML = '<div class="composition-empty">Erreur serveur</div>';
        });
    };

    function renderCompositionList(compositions) {
        const list = document.getElementById('compositionList');
        if (!compositions.length) {
            list.innerHTML = '<div class="composition-empty">Aucun article dans ce kit</div>';
            return;
        }
        list.innerHTML = compositions.map(function(item) {
            return '<div class="composition-item" data-code="' + item.code_composition + '">' +
                '<div class="composition-item-info">' +
                    '<strong>' + escapeHtml(item.article_libelle) + '</strong>' +
                    '<small>' + item.article_code + '</small>' +
                '</div>' +
                '<div class="composition-item-actions">' +
                    '<span class="quantite">x' + item.quantite + '</span>' +
                    '<button type="button" class="btn-icon composition-remove" title="Retirer" data-code="' + item.code_composition + '">' +
                        '<i data-lucide="trash-2"></i>' +
                    '</button>' +
                '</div>' +
            '</div>';
        }).join('');
        lucide.createIcons();
    }

    document.getElementById('compositionClose')?.addEventListener('click', function() {
        const panel = document.getElementById('compositionPanel');
        if (panel) {
            panel.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    document.getElementById('compositionAddBtn')?.addEventListener('click', function() {
        const panel = document.getElementById('compositionPanel');
        const kitCode = panel ? panel.dataset.kitCode : '';
        const articleCode = document.getElementById('compositionArticle').value;
        const quantite = parseInt(document.getElementById('compositionQuantite').value, 10);
        if (!kitCode) {
            showToast('Kit introuvable', 'error');
            return;
        }
        if (!articleCode) {
            showToast('Veuillez sélectionner un article', 'error');
            return;
        }
        if (!quantite || quantite < 1) {
            showToast('Quantité invalide', 'error');
            return;
        }
        const btn = this;
        loading(btn, true, '<i class="fa fa-spinner fa-spin"></i>');
        $.post(LINK + 'kit/addComposition', {
            kit_code: kitCode,
            article_code: articleCode,
            quantite: quantite,
            csrf_token: $('input[name="csrf_token"]').val()
        }, function(resp) {
            loading(btn, false, '<i data-lucide="plus"></i> Ajouter');
            if (resp && resp.status) {
                showToast(resp.message, 'success');
                window.openCompositionPanel(kitCode);
            } else {
                showToast(resp ? resp.message : 'Erreur', 'error');
            }
        }, 'json').fail(function(xhr) {
            loading(btn, false, '<i data-lucide="plus"></i> Ajouter');
            let errorMsg = 'Erreur serveur';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showToast(errorMsg, 'error');
        });
    });

    $(document).on('click', '.composition-remove', function() {
        const codeComposition = this.getAttribute('data-code');
        const panel = document.getElementById('compositionPanel');
        const kitCode = panel ? panel.dataset.kitCode : '';
        if (!codeComposition || !kitCode) return;

        const confirmModal = document.getElementById('confirmModal');
        const confirmTitle = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmOk = document.getElementById('confirmOk');

        if (!confirmModal) {
            if (!confirm('Retirer cet article du kit ?')) return;
            proceedRemove();
            return;
        }

        confirmTitle.textContent = 'Retirer l\'article';
        confirmMessage.textContent = 'Voulez-vous vraiment retirer cet article du kit ?';

        const closeConfirm = function() {
            confirmModal.classList.remove('active');
        };

        if (!confirmModal._bound) {
            confirmModal._bound = true;
            document.getElementById('confirmClose').addEventListener('click', closeConfirm);
            document.getElementById('confirmCancel').addEventListener('click', closeConfirm);
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) closeConfirm();
            });
        }

        confirmOk.onclick = function() {
            closeConfirm();
            proceedRemove();
        };

        confirmModal.classList.add('active');

        function proceedRemove() {
            $.post(LINK + 'kit/removeComposition', {
                code_composition: codeComposition,
                csrf_token: $('input[name="csrf_token"]').val()
            }, function(resp) {
                if (resp && resp.status) {
                    showToast(resp.message, 'success');
                    window.openCompositionPanel(kitCode);
                } else {
                    showToast(resp ? resp.message : 'Erreur', 'error');
                }
            }, 'json').fail(function(xhr) {
                let errorMsg = 'Erreur serveur';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showToast(errorMsg, 'error');
            });
        }
    });

    document.addEventListener('click', function(e) {
        const panel = document.getElementById('compositionPanel');
        if (!panel) return;
        if (panel.classList.contains('active') && !panel.contains(e.target) && !e.target.closest('.composeKit')) {
            panel.classList.remove('active');
            document.body.style.overflow = '';
            document.querySelectorAll('.composeKit').forEach(function(b) { b.classList.remove('active'); });
        }
    });

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});
