jQuery(document).ready(function ($) {
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover'
    });

    $(document).on('click', '.order-btn', function () {
        var element = $(this);
        var id = parseInt(element.data('id'));
        var order = parseInt(element.data('order'));
        var direction = parseInt(element.data('direction'));

        $.ajax({
            url: ajax.url,
            type: 'post',
            data: {
                action: 'change_order',
                id: id,
                order: order,
                direction: direction
            }
        }).then(function (response) {
            if (response.status) {
                $('#categories').html(response.html);
            }
        });
    });

    $(document).on('click', '.btn-edit', function () {

        var element = $(this);
        var modal = $('#main-modal');
        var id = element.data('id');

        $.ajax({
            url: ajax.url,
            type: 'post',
            data: {
                action: 'get_edit_content',
                id: id
            }
        }).then(function (response) {
            if (response.status) {
                modal.find('.modal-title').html('Muuda kategooriat');
                modal.find('.modal-body').html(response.html);
                modal.modal();
            }
        });
    });

    $(document).on('click', '#create-cat-btn', function () {
        var modal = $('#main-modal');

        $.ajax({
            url: ajax.url,
            type: 'post',
            data: {
                action: 'get_create_content',
            }
        }).then(function (response) {
            if (response.status) {
                modal.find('.modal-title').html('Loo uus kategooria');
                modal.find('.modal-body').html(response.html);
                modal.modal();
            }
        });

    });


    $(document).on('submit', '#create-form', function (e) {
        e.preventDefault();
        var form = $(this);

        $.post({
            url: ajax.url,
            data: {
                action: 'create_category',
                data: form.serialize()
            }
        }).then(function (response) {
            if (response.status) {
                $('#categories').html(response.html);
                $('#main-modal').modal('hide');
            }
        });
    });


    $(document).on('submit', '#edit-form', function (e) {
        e.preventDefault();
        var form = $(this);

        $.post({
            url: ajax.url,
            data: {
                action: 'update_category',
                data: form.serialize()
            }
        }).then(function (response) {
            if (response.status) {
                $('#categories').html(response.html);
                $('#main-modal').modal('hide');
            }
        });
    });

    $(document).on('click', '.delete-category', function () {
        var element = $(this);
        var id = element.data('id');

        swal({
            title: 'Olete kindel et soovite kategooria kustutada?',
            allowOutsideClick: true,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Jah",
            cancelButtonText: 'Loobu',
            closeOnConfirm: false,
            html: false
        }, function () {
            $.post({
                url: ajax.url,
                data: {
                    action: 'delete_category',
                    id: id
                }
            }).then(function (response) {
                if (response.status) {
                    $('#categories').html(response.html);
                    swal("Kustutatud!", "Antud kategooria on eemaldatud", "success");
                }
            });
        })
    });


    $(document).on('input', '#parent-select', function () {
        var parent_id = $(this).val();

        $.post({
            url: ajax.url,
            data: {
                action: 'get_sibling_count',
                parent_id: parent_id
            }
        }).then(function (response) {
            $('#order-select').html(response.html);
        });
    });

});

