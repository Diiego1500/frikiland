$('.add_favorite').click(function () {
    var favorite = event.target;
    post_id = favorite.id;

    Swal.fire({
        title: 'Agregar a favoritos?',
        showCancelButton: true,
        confirmButtonText: '¡Sí,Agregalo!',
        cancelButtonText: 'No, cancelar!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('postSave', { id: post_id }),
                data: ({post_id: post_id}),
                async: true,
                dataType: "json",
                success: function (data) {
                    Swal.fire(
                        '¡Agregado!',
                        'Ahora este post está en tus favoritos',
                        'success'
                    )
                }
            })
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            document.getElementById(user_id).checked = !clase.checked;
            Swal.fire(
                'Cancelado',
                'No se ha agregado a favoritos',
                'error'
            )
        }
    })
})


$('.add_like').click(function (){
    var like = event.target;
    post_id = $('#like_btn').attr("data-id")
    $.ajax({
        type: 'POST',
        url: Routing.generate('postLike', { id: post_id }),
        data: ({post_id: post_id}),
        async: true,
        dataType: "json",
        success: function (data) {
            console.log(data)
            var like_value = data['like_value'];
            if (like_value === true) {
                $('#like_btn').css('color','#3747be');
                $('#like_btn_mobile').css('color','#3747be');
            }else {
                $('#like_btn').css('color', '#c2c1c1');
                $('#like_btn_mobile').css('color', '#c2c1c1');
            }
            $('#likes_ammount').text(data['likes_ammount'])
            $('#likes_ammount_mobile').text(data['likes_ammount'])
        }
    })
})

$('#delete').click(function (){
    var post_id = $(this).attr("data-id")
    Swal.fire({
        title: '¿Deseas eliminar el Post?',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            window.location.href = Routing.generate('postDelete', { id: post_id })
        }
    })
})

$('#delete-sm').click(function (){
    var post_id = $(this).attr("data-id")
    Swal.fire({
        title: '¿Deseas eliminar el Post?',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            window.location.href = Routing.generate('postDelete', { id: post_id })
        }
    })
})

$('.image_thumb').click(function () {
    var post_id = $(this).attr("data-id");
    $.ajax({
        type: 'POST',
        url: Routing.generate('ajaxPostDetails', { id: post_id }),
        data: ({post_id: post_id}),
        async: true,
        dataType: "json",
        success: function (data) {
            Swal.fire({
                text: data['post_title'],
                imageUrl: '../uploads/files/' + data['post_image'],
                imageWidth: 500,
                showConfirmButton: false,
            })
            console.log(data['post_image'])
        }
    })
})