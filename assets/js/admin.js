function updateUserStatus(matricule, status, userType) {
    $.ajax({
        url: 'update_user_status.php',
        type: 'POST',
        data: { matricule: matricule, status: status, userType: userType },
        success: function(response) {
            var result = JSON.parse(response);
            showMessage(result.message, result.success);
            if (result.success) {
                // Update the status in the table without reloading
                $('tr[data-matricule="' + matricule + '"] td:nth-child(4)').text(status);
            }
        }
    });
}

function supprimerCours(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
        $.ajax({
            url: 'del_cours_admin.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                var result = JSON.parse(response);
                showMessage(result.message, result.success);
                if (result.success) {
                    // Remove the course row from the table without reloading
                    $('tr[data-course-id="' + id + '"]').remove();
                }
            }
        });
    }
}

$('#categorieForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
        url: 'cat_admin.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            var result = JSON.parse(response);
            showMessage(result.message, result.success);
            if (result.success) {
                $('#categorieForm')[0].reset();
                $('#categorie_id').val('');
                // Refresh the categories table without reloading
                refreshCategoriesTable();
            }
        }
    });
});

function supprimerCategorie(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
        $.ajax({
            url: 'del_cat_admin.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                var result = JSON.parse(response);
                showMessage(result.message, result.success);
                if (result.success) {
                    // Remove the category row from the table without reloading
                    $('tr[data-category-id="' + id + '"]').remove();
                }
            }
        });
    }
}

$('#tagForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
        url: 'tag_admin.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            var result = JSON.parse(response);
            showMessage(result.message, result.success);
            if (result.success) {
                $('#tagForm')[0].reset();
                $('#tag_id').val('');
                // Refresh the tags table without reloading
                refreshTagsTable();
            }
        }
    });
});

function supprimerTag(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce tag ?')) {
        $.ajax({
            url: 'del_tag_admin.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                var result = JSON.parse(response);
                showMessage(result.message, result.success);
                if (result.success) {
                    // Remove the tag row from the table without reloading
                    $('tr[data-tag-id="' + id + '"]').remove();
                }
            }
        });
    }
}

// Helper functions to refresh tables
function refreshCategoriesTable() {
    $.ajax({
        url: 'get_categories.php',
        type: 'GET',
        success: function(response) {
            $('#categories table tbody').html(response);
        }
    });
}

function refreshTagsTable() {
    $.ajax({
        url: 'get_tags.php',
        type: 'GET',
        success: function(response) {
            $('#tags table tbody').html(response);
        }
    });
}

