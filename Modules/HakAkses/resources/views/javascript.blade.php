<script type="text/javascript">
    $(document).ready(function() {
        getRole();
    });

    function loadHakAkses(element){
        var roleId = $(element).data('role_id');
        $('#role_id_val').val(roleId).trigger('change');
        blockPage();
		$.ajax({
			url: '{{ route('hakakses.getMenuList') }}',
            type: 'POST',
			data: {
				roleId: roleId
			},
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
			success: (response) => {
                // Destroy any existing jstree instance before initializing a new one
                if ($('#dataTree').jstree(true)) {
                    $('#dataTree').jstree('destroy').empty();
                }
				$('#dataTree').jstree({
					'plugins': ["checkbox", "types", 'wholerow', "massload", "search"],
					'core': {
						'data': response['menu'],
					},
					"types": {
						"default": {
							"icon": "fa fa-folder text-success"
						},
						"file": {
							"icon": "fa fa-file text-success"
						}
					},
					"search": {
						"case_sensitive": false,
						"show_only_matches": true,
						"delay": 100,
					},
				});

				$('#dataTree').on("changed.jstree", function(e, data) {
					if (typeof data.node != 'undefined') {
						if (data.selected.length == 0) {
							$("#btnSaveHA").addClass('d-none');
						} else {
							$("#btnSaveHA").removeClass('d-none');
						}
						unique = [];
						let itemList = data.selected;
						//adding all parent
						$.each(itemList, function(i, el) {
							if (data.instance.is_leaf(el)) {
								$.each(data.instance.get_node(el).parents, function(i2, el2) {
									if ($.inArray(el2, itemList) == -1 && el2 != '#') itemList.push(el2);
								});
							}
						});
						unique = itemList;
					}
				});
			},
			complete: (response) => {
				$('#search').keyup(function(event) {
					let tree = $("#dataTree").jstree(true);
					tree.search(this.value);
					$('#dataTree .no-data').remove();
					// Check if there are no search results
					if ($("#dataTree").find('.jstree-search').length === 0) {
						// Display "No data" message or handle it as per your UI structure
						$('#dataTree .jstree-container-ul').hide()
						$('#dataTree .jstree-container-ul').after('<div class="no-data text-center py-5">Data tidak ditemukan</div>');
					} else {
						// Remove the "No data" message if there are search results
						$('#dataTree .jstree-container-ul').show()
					}
				});
				unblockPage(200);
			}
		})
    }

    function getRole(){
        $.ajax({
            url: '{{ route('hakakses.getRoleList') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: (response) => {
                $('#tbody-role').empty().html(atob(response.roles));
            }
        });
    }

    function saveHakAkses(){
        SUPER.confirm({
			message: "Are you sure you want to save it?",
			callback: (result) => {
				if (result) {
					blockPage();
					$.ajax({
						url: '{{ route('hakakses.create') }}',
						data: {
							'role_id': $("[name=role_id_val]").val(),
							'roles': ((unique))
						},
						type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
						success: (response) => {
							if (response.success) {
								SUPER.showMessage({
									success: true,
									title: 'Information',
									message: 'Successfully saved data.'
								})
								if (response.reload) {
									window.location.reload();
								}
							}
						},
						complete: (response) => {
							unblockPage(200);
						}
					})
				}
			}
		});
    }
</script>
