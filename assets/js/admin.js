/**
 * Admin JavaScript para Replanta Author SEO
 * 
 * Avatar uploader y media library integration
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Avatar Uploader
         */
        var avatarUploader;
        
        $('.replanta-upload-avatar-button').on('click', function(e) {
            e.preventDefault();
            
            // Si el uploader ya existe, abrirlo
            if (avatarUploader) {
                avatarUploader.open();
                return;
            }
            
            // Crear nuevo media uploader
            avatarUploader = wp.media({
                title: replantaAvatarUploader.title || 'Seleccionar Avatar',
                button: {
                    text: replantaAvatarUploader.button || 'Usar como Avatar'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // Cuando se selecciona una imagen
            avatarUploader.on('select', function() {
                var attachment = avatarUploader.state().get('selection').first().toJSON();
                
                // Actualizar input hidden
                $('#replanta_custom_avatar').val(attachment.id);
                
                // Actualizar preview
                var previewHtml = '<img src="' + attachment.url + '" alt="Avatar" style="max-width: 150px; height: auto; border-radius: 50%;">';
                $('.avatar-preview').html(previewHtml);
                
                // Mostrar botón de eliminar si no existe
                if ($('.replanta-remove-avatar-button').length === 0) {
                    var removeButton = '<button type="button" class="button replanta-remove-avatar-button">Eliminar Avatar</button>';
                    $('.replanta-upload-avatar-button').after(removeButton);
                }
            });
            
            avatarUploader.open();
        });
        
        /**
         * Eliminar Avatar
         */
        $(document).on('click', '.replanta-remove-avatar-button', function(e) {
            e.preventDefault();
            
            if (!confirm('¿Estás seguro de eliminar el avatar personalizado?')) {
                return;
            }
            
            // Limpiar input
            $('#replanta_custom_avatar').val('');
            
            // Restaurar placeholder
            var placeholderHtml = '<div class="avatar-placeholder" style="width: 150px; height: 150px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><span style="color: #999; font-size: 14px;">Sin avatar</span></div>';
            $('.avatar-preview').html(placeholderHtml);
            
            // Ocultar botón de eliminar
            $(this).remove();
        });
        
        /**
         * Generic Image Upload para settings
         */
        var imageUploader;
        
        $('.replanta-upload-button').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetInput = button.data('target');
            
            // Crear media uploader
            imageUploader = wp.media({
                title: 'Seleccionar Imagen',
                button: {
                    text: 'Usar esta imagen'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // Cuando se selecciona imagen
            imageUploader.on('select', function() {
                var attachment = imageUploader.state().get('selection').first().toJSON();
                
                // Actualizar input
                $('#' + targetInput).val(attachment.url);
                
                // Actualizar preview
                button.siblings('.image-preview').html('<img src="' + attachment.url + '" alt="" style="max-width: 200px;">');
                
                // Mostrar botón eliminar
                if (button.siblings('.replanta-remove-button').length === 0) {
                    button.after('<button type="button" class="button replanta-remove-button" data-target="' + targetInput + '">Eliminar</button>');
                }
            });
            
            imageUploader.open();
        });
        
        /**
         * Eliminar imagen genérica
         */
        $(document).on('click', '.replanta-remove-button', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetInput = button.data('target');
            
            // Limpiar input
            $('#' + targetInput).val('');
            
            // Limpiar preview
            button.siblings('.image-preview').empty();
            
            // Ocultar botón
            button.remove();
        });
        
        /**
         * Settings Tabs (si se necesitan en el futuro)
         */
        $('.nav-tab').on('click', function(e) {
            var $this = $(this);
            
            // Prevenir default solo si es un anchor con hash
            if ($this.attr('href').indexOf('#') !== -1) {
                e.preventDefault();
            }
        });
        
    });

})(jQuery);
