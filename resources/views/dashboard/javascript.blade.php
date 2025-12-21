<script type="text/javascript">
    $(document).ready(function() {
        SUPER.set_role_access(<?php echo $roles?>);
        // pusher();
        router();
    });

    function router(){
        const router = new Navigo("/dashboard");
        router.on("/", function () {
            var page = 'home';
            loadPage(page);
        });

        router.on("/:page", function (params) {
            var check = SUPER.get_role_access(params.data.page);
            if(check){
                loadPage(params.data.page);
            }else{
                loadPage('home');
            }
        });

        router.resolve();
    }

    function loadPage(page) {
        blockPage();
        $.ajax({
            url: "{{ route('loadpage') }}",
            data: {
                destination: page,
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            type: 'POST',
            success: function (result) {
                var base64 = result.page;
                var decoded = atob(base64);
                $.when(function () {
                    $("#kt_post").empty().html(decoded);
                    // Remove 'active' class and collapse all accordions
                    $(".menu-link, .menu-item, .menu-accordion").removeClass("active show hover");
                    // Add 'active' class to the clicked item and its parent menu items
                    const clickedItem = $('#lnk-' + page);
                    clickedItem.addClass('active');
                    clickedItem.parents('.menu-item').addClass('active show hover');
                    clickedItem.parents('.menu-accordion').addClass('active show hover');
                    // Collapse all unrelated accordions
                    $(".menu-accordion").not(clickedItem.parents('.menu-accordion')).removeClass('show');
                    $(".menu-accordion .menu-sub").not(clickedItem.parents('.menu-sub')).css({
                        display: 'none',
                        overflow: 'hidden'
                    });
                    // Ensure the clicked accordion expands
                    clickedItem.closest('.menu-sub').css({
                        display: 'block',
                        overflow: 'visible'
                    });
                    // Update page title
                    const pagename = clickedItem.data('page');
                    $('#ttl-header').text(pagename);
                    $('#ttl').text('ALRC - Portal Catur Dharma - ' + pagename);
                }()).then(function () {
                    const container = $("#kt_post");
                    $.each($('[data-roleable=true]', container), function (i, v) {
                        if ($(v).data('role') !== 'undefined' && $(v).data('role') !== '') {
                            const roles = $(v).data('role').split('|');
                            let checkRole = true;
                            $.each(roles, function (iR, vR) {
                                if (SUPER.get_role_access(vR)) {
                                    checkRole = false;
                                }
                            });
                            if (checkRole) {
                                if ($(v).data('action') !== 'undefined' && $(v).data('action') === 'hide') {
                                    $(v).hide();
                                } else {
                                    $(v).remove();
                                }
                            }
                        }
                    });
                }()).then(function () {
                    $("#kt_post").css('visibility', 'visible');
                    unblockPage();
                }());
            },
            error: function (xhr, status, error) {
                if (xhr.status === 419 || xhr.status === 401) {
                    $.confirm({
                        title: 'Failed',
                        content: 'Session Over',
                        theme: 'material',
                        type: 'red',
                        buttons: {
                            ok: {
                                text: "ok!",
                                btnClass: config.btnClass,
                                keys: ['enter'],
                            }
                        }
                    });
                    setLogout();
                } else if (xhr.status === 404) {
                    var html = '<div class="kt-grid kt-grid--ver kt-grid--root"><div class="kt-error404-v1"><div class="kt-error404-v1__content"><div class="kt-error404-v1__title">404</div><div class="kt-error404-v1__desc"><strong>OOPS!</strong> Halaman tidak ditemukan.</div></div><div class="kt-error404-v1__image"><img src="theme/assets/media/misc/404-bg1.jpg" style="height: 100px;" class="kt-error404-v1__image-content" alt="" title="" /></div></div></div>';
                    $("#kt_post").empty().html(html);
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                    unblockPage();
                } else if (xhr.status === 502) {
                    window.location.reload();
                } else if (xhr.status === 500) {
                    var html = '<div class="kt-grid kt-grid--ver kt-grid--root"><div class="kt-error500-v1"><div class="kt-error500-v1__content"><div class="kt-error500-v1__title">500</div><div class="kt-error500-v1__desc"><strong>OOPS!</strong> Server error.</div></div><div class="kt-error500-v1__image"><img src="theme/assets/media/misc/500-bg1.jpg" style="height: 100px;" class="kt-error500-v1__image-content" alt="" title="" /></div></div></div>';
                    $("#kt_post").empty().html(html);
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                    unblockPage();
                }
            }
        });
    }

    function blockPage(message = 'Loading...') {
        $.blockUI({
            message: `<div class="blockui-message" style="z-index: 11000">
                        <span class="spinner-border text-primary"></span> ${message}
                    </div>`,
            css: {
                border: 'none',
                backgroundColor: 'rgba(47, 53, 59, 0)',
                'z-index': 11000 // Ensure the message is above the modal
            },
            overlayCSS: {
                backgroundColor: 'rgba(0, 0, 0, 0.5)', // Optional: semi-transparent black overlay
                'z-index': 10999 // Ensure overlay is above modal backdrop
            }
        });
    }

    function unblockPage(delay = 500){
        window.setTimeout(function () {
            $.unblockUI();
        }, delay);
    }

    function logout(){
        $.ajax({
            url: "",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(result){
                window.location.href = "";
            }
        });

    }

    $.fn.serializeObject = function(){
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

    var SUPER = function(){

        return {
            set_role_access: function (data=[]) {
				role_access = data;
			},

			get_role_access: function (name=null) {
				if (name) {
					if (role_access) {
						return role_access.includes(name);
					}
					return false;
				}
				return role_access;
			},

            confirm: function (config) {

                config = $.extend(true, {
                    title: 'Information',
                    message: null,
                    size: 'small',
                    type: 'blue',
                    confirmLabel: '<i class="fa fa-check"></i> Yes',
                    confirmClassName: 'btn btn-focus btn-success m-btn m-btn--pill m-btn--air',
                    cancelLabel: '<i class="fa fa-times"></i> No',
                    cancelClassName: 'btn btn-focus btn-danger m-btn m-btn--pill m-btn--air',
                    showLoaderOnConfirm: false,
                    allowOutsideClick: true,
                    callback: function () { }
                }, config);

                $.confirm({
                    title: config.title,
                    content: config.message,
                    theme: 'material',
                    type: config.type,
                    buttons: {
                        ok: {
                            text: "ok!",
                            btnClass: 'btn-primary',
                            keys: ['enter'],
                            action: function () {
                                config.callback(true);
                            }
                        }, cancel: function () {
                            config.callback(false);
                        }
                    }
                });
            },

            showMessage: function (config) {
                config = $.extend(true, {
                    success: false,
                    message: 'System error, please contact the Administrator',
                    title: 'Failed',
                    time: 5000,
                    sticky: false,
                    allowOutsideClick: true,
                    toast: false,
                    type: 'blue',
                    btnClass: 'btn-primary',
                    callback: function () { },
                }, config);
                if (config.success == true) {
                    $.confirm({
                        title: (config.title == "Failed") ? "Success" : config.title,
                        content: config.message,
                        theme: 'material',
                        type: config.type,
                        buttons: {
                            ok: {
                                text: "ok!",
                                btnClass: config.btnClass,
                                keys: ['enter'],
                                action: function () {
                                    config.callback(true);
                                }
                            }
                        }
                    });
                } else {
                    $.confirm({
                        title: config.title,
                        content: config.message,
                        theme: 'material',
                        type: 'red',
                        buttons: {
                            ok: {
                                text: "ok!",
                                btnClass: config.btnClass,
                                keys: ['enter'],
                                action: function () {
                                    config.callback(true);
                                }
                            }
                        }
                    });
                }
            },

            trim_string: function(originalString, maxlength = 30, change = '...') {
                if (!originalString) return ""; // Handle empty string

                var truncatedString = originalString.length > maxlength
                    ? originalString.substring(0, maxlength) + change
                    : originalString;

                return truncatedString;
            },

            // Combobox

            setDataMultipleCombo: function (data) {
                $.each(data, function (i, v) {
                    SUPER.setChangeCombo(v);
                });
            },

            setChangeCombo: function (config) {
                config = $.extend(true, {
                    el: null,
                    data: {},
                    valueField: null,
                    valueAdd: null,
                    displayField: null,
                    displayField2: null,
                    grouped: false,
                    withNull: true,
                    withNullDisabled: true,
                    idMode: true,
                    placeholder: '',
                    dropdownParent: false,
                    select2: false,
                }, config);

                if (config.idMode === true) {
                    var html = (config.withNull === true) ? "<option value='' selected " + ((config.withNullDisabled) ? 'disabled' : '') + ">" + config.placeholder + "</option>" : "";
                    $.each(config.data, function (i, v) {
                        var vAdd = '';
                        if (v[config.valueAdd]) {
                            vAdd = " data-add='" + v[config.valueAdd] + "'";
                        }
                        if (config.grouped) {
                            if (config.displayField3 != null) {
                                html += "<option value='" + v[config.valueField] + "' " + vAdd + ">" + v[config.displayField2] + " - " + v[config.displayField] + " ( " + v[config.displayField3] + " ) " + "</option>";
                            } else {
                                html += "<option value='" + v[config.valueField] + "' " + vAdd + ">" + v[config.displayField2] + " - " + v[config.displayField] + "</option>";
                            }
                        } else {
                            html += "<option value='" + v[config.valueField] + "' " + vAdd + ">" + v[config.displayField] + "</option>";
                        }
                    });
                    $('#' + config.el).html(html);
                    if (config.select2) {
                        $('#' + config.el).select2($.extend(true, {
                            allowClear: true,
                            dropdownAutoWidth: true,
                            width: '100%',
                            placeholder: config.placeholder,
                        }, config.dropdownParent ? {
                            dropdownParent: $('#' + config.el).parents('.modal').first(),
                        } : {}));
                    }
                } else {
                    var html = (config.withNull === true) ? "<option value='' selected " + ((config.withNullDisabled) ? 'disabled' : '') + ">" + config.placeholder + "</option>" : "";
                    $.each(config.data, function (i, v) {
                        var vAdd = '';
                        if (v[config.valueAdd]) {
                            vAdd = " data-add='" + v[config.valueAdd] + "'";
                        }
                        if (config.grouped) {
                            if (config.displayField3 != null) {
                                html += "<option value='" + v[config.valueField] + "' " + vAdd + ">" + v[config.displayField2] + " - " + v[config.displayField] + " ( " + v[config.displayField3] + " ) " + "</option>";
                            } else {
                                html += "<option value='" + v[config.valueField] + "' " + vAdd + ">" + v[config.displayField2] + " - " + v[config.displayField] + "</option>";
                            }
                        } else {
                            html += "<option value='" + v[config.valueField] + "' " + vAdd + ">" + v[config.displayField] + "</option>";
                        }
                    });
                    $(config.el).html(html);
                    if (config.select2) {
                        $(config.el).map((i, e) => {
                            $(e).select2($.extend(true, {
                                allowClear: true,
                                dropdownAutoWidth: true,
                                width: '100%',
                                placeholder: config.placeholder,
                            }, config.dropdownParent ? {
                                dropdownParent: $(e).parents('.modal').first(),
                            } : {}));
                        })
                    }
                }
            },

            saveForm: function(config){
				config = $.extend(true, {
					element  : null,
					checker : null,
					add_route: null,
					update_route : null,
                    update_route_override : null,
                    onBack: null,
                    reInitTable: null,
                    file_upload: null,
                    file_fields: null,
					callback: function(args){}
				}, config);
				var id = $('#'+config.checker).val();
				// Penentuan URL dan Tipe Protokol
				var alamat = '';
				var protocol = '';
				if(jQuery.isEmptyObject(id)){
					alamat = config.add_route;
					protocol = 'POST';
				}else if(!jQuery.isEmptyObject(id) && config.update_route_override == null){
					alamat = config.update_route;
					protocol = 'PUT';
				}else if(!jQuery.isEmptyObject(id) && config.update_route_override != null){
                    alamat = config.update_route;
                    protocol = config.update_route_override;
                }
				// Konfirmasi
                SUPER.confirm({
                    message: 'You are going to save/change the data. Are you sure ?',
                    callback: (result) => {
                        if(result){
                            blockPage();
                            if(config.file_upload){
                                let formData = new FormData();
                                formData.append('form', JSON.stringify($('[name=' + config.element + ']').serializeObject()));
                                $.each(config.file_fields, function(i, file) {
                                    formData.append('file[' + i + ']', $('[name=' + file + ']')[0].files[0]);
                                });
                                $.ajax({
                                    url: alamat,
                                    type: protocol,
                                    contentType: false,
                                    processData: false,
                                    data: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    success: function(lar){
                                        if(lar.success == true){
                                            SUPER.showMessage({
                                                success: true,
                                                message: 'Penyimpanan sukses !',
                                                title: 'Sukses'
                                            });
                                            $('#'+config.element)[0].reset();
                                            if(config.onBack != null){
                                                onBack();
                                            }
                                            if(config.callback){
                                                config.callback();
                                            }
                                        }else{
                                            SUPER.showMessage({
                                                success: false,
                                                message: lar.message,
                                                title: 'Gagal'
                                            });
                                            onRefresh();
                                            if(config.callback){
                                                config.callback();
                                            }
                                        }
                                    },
                                    error: function(xhr, status, error){
                                        console.log(xhr);
                                        var msg = 'System error, silakan hubungi Administrator';
                                        if(xhr.responseJSON.message){
                                            msg = xhr.responseJSON.message;
                                        }
                                        SUPER.showMessage({
                                            success: false,
                                            message: msg,
                                            title: 'Gagal'
                                        });
                                        onRefresh();
                                        if(config.callback){
                                            config.callback();
                                        }
                                    }
                                });
                            }else{
                                $.ajax({
                                    url: alamat,
                                    type: protocol,
                                    data: $('[name=' + config.element + ']').serializeObject(),
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    success: function(lar){
                                        if(lar.success == true){
                                            SUPER.showMessage({
                                                success: true,
                                                message: 'Penyimpanan sukses !',
                                                title: 'Sukses'
                                            });
                                            $('#'+config.element)[0].reset();
                                            if(config.onBack != null){
                                                onBack();
                                            }
                                            if(config.callback){
                                                config.callback();
                                            }
                                        }else{
                                            SUPER.showMessage({
                                                success: false,
                                                message: lar.message,
                                                title: 'Gagal'
                                            });
                                            onRefresh();
                                            if(config.callback){
                                                config.callback();
                                            }
                                        }
                                    },
                                    error: function(xhr, status, error){
                                        console.log(xhr);
                                        var msg = 'System error, silakan hubungi Administrator';
                                        if(xhr.responseJSON.message){
                                            msg = xhr.responseJSON.message;
                                        }
                                        SUPER.showMessage({
                                            success: false,
                                            message: msg,
                                            title: 'Gagal'
                                        });
                                        onRefresh();
                                        if(config.callback){
                                            config.callback();
                                        }
                                    }
                                });
                            }
                            if(config.reInitTable != null){
                                init_table();
                            }
                            unblockPage();
                        }else{
                            SUPER.showMessage({
                                success: false,
                                message: 'Penyimpanan dibatalkan',
                                title: 'Batal'
                            });
                            if(config.callback){
                                config.callback();
                            }
                        }
                    }
                });
			},

            switchForm: function(config){
				config = $.extend(true, {
					speed: 'fast',
					easing: 'swing',
					callback: function() {},
					tohide: 'table_data',
					toshow: 'form_data',
					animate: null,
				}, config);

				if (config.animate!==null)
				{
					if (config.animate==='fade')
					{
						$("." + config.tohide).fadeOut(config.speed, function() {
							$("." + config.toshow).fadeIn(config.speed, config.callback)
						});
					}
					else if (config.animate==='toogle')
					{
						$("." + config.tohide).fadeToggle(config.speed, function() {
							$("." + config.toshow).fadeToggle(config.speed, config.callback)
						});
					}
					else if (config.animate==='slide')
					{
						$("." + config.tohide).slideUp(config.speed, function(){
							$("." + config.toshow).slideDown(config.speed,config.callback);
						});
					}
					else{
						$("." + config.tohide).fadeOut(config.speed, function() {
							$("." + config.toshow).fadeIn(config.speed, config.callback)
						});
					}
				}
				else
				{
					$("." + config.tohide).fadeOut(config.speed, function() {
						$("." + config.toshow).fadeIn(config.speed, config.callback)
					});
				}

				$('html,body').animate({
					scrollTop: 0 /*pos + (offeset ? offeset : 0)*/
				}, 'slow');
			},

            formatDate: function (dateString) {
                var date = new Date(dateString);
                var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                var day = date.getDate();
                var month = months[date.getMonth()];
                var year = date.getFullYear();
                var hours = date.getHours();
                var minutes = date.getMinutes();
                var seconds = date.getSeconds();

                // Check if the dateString includes a time component
                var hasTime = dateString.includes('T') || dateString.includes(' ');

                // Adjusting to local timezone
                var timezoneOffset = date.getTimezoneOffset() / 60;
                hours += timezoneOffset;

                // Correcting hours if negative or exceeding 24
                if (hours < 0) {
                    hours += 24;
                    day -= 1; // Subtract one day
                } else if (hours >= 24) {
                    hours -= 24;
                    day += 1; // Add one day
                }

                // Adding leading zeros if needed
                if (day < 10) day = '0' + day;
                if (hours < 10) hours = '0' + hours;
                if (minutes < 10) minutes = '0' + minutes;
                if (seconds < 10) seconds = '0' + seconds;

                // Construct formatted date
                var formattedDate = day + ' ' + month + ' ' + year;
                if (hasTime) {
                    formattedDate += ' ' + hours + ':' + minutes + ':' + seconds;
                }

                return formattedDate;
            },

            ntr(number) {
                // Convert the number to a string and split by the decimal point
                let number_string = number.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    remainder = split[0].length % 3,
                    rupiah = split[0].substr(0, remainder),
                    thousand = split[0].substr(remainder).match(/\d{3}/gi);

                // Add dots as thousand separators
                if (thousand) {
                    separator = remainder ? '.' : '';
                    rupiah += separator + thousand.join('.');
                }

                // Join the rupiah with the decimal part if it exists
                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                // return 'Rp' + rupiah;
                return rupiah;
            },
        }
    }();

</script>
