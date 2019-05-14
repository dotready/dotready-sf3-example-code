if (typeof SI === 'undefined') {
    var SI = null || {};
}

SI.Pipeline = function() {

    var organisations = [];

    var rearrange = function() {

        var stages, input, i;

        if (!document.getElementById('stages')) {
            console.log('stages are missing');
            return;
        }

        if (!document.getElementById('dummy')) {
            console.log('dummy is missing');
            return;
        }

        stages = document.getElementById('stages').getElementsByTagName('li');

        for (i = 0; i < stages.length; i++) {
            if (stages[i].id === 'dummy') {
                continue;
            }

            input = stages[i].getElementsByClassName('stage[index]');
            input[0].value = i;
        }
    };

    var stage = {
        save: function(stage) {

            console.log(stage);

            var stageId = '';

            if (typeof stage !== 'undefined') {
                stageId = 'stage_' + stage.id;
            }

            var clone = document.getElementById('dummy').cloneNode(true);
            var index = document.getElementById('stages').getElementsByTagName('li').length -1;

            clone.id = stageId;

            clone.getElementsByClassName('name')[0].innerHTML = document.getElementById('stage_name').value;
            clone.getElementsByClassName('stage[name]')[0].value = document.getElementById('stage_name').value;
            clone.getElementsByClassName('stage[name]')[0].name = 'stages['+index+'][name]';

            clone.getElementsByClassName('stage[probability]')[0].value = document.getElementById('stage_probability').value;
            clone.getElementsByClassName('stage[probability]')[0].name = 'stages['+index+'][probability]';

            clone.getElementsByClassName('stage[index]')[0].value = index;
            clone.getElementsByClassName('stage[index]')[0].name = 'stages['+index+'][index]';

            if (typeof stage !== 'undefined') {
                clone.getElementsByClassName('stage[id]')[0].value = stage.id;
                clone.getElementsByClassName('stage[id]')[0].name = 'stages[' + index + '][id]';

                clone.getElementsByClassName('stage[pipelineId]')[0].value = stage.pipelineId;
                clone.getElementsByClassName('stage[pipelineId]')[0].name = 'stages[' + index + '][pipelineId]';

                clone.getElementsByClassName('stage[created]')[0].value = stage.created;
                clone.getElementsByClassName('stage[created]')[0].name = 'stages[' + index + '][created]';

                clone.getElementsByClassName('stage[archived]')[0].value = 0;
                clone.getElementsByClassName('stage[archived]')[0].name = 'stages[' + index + '][archived]';

                clone.getElementsByClassName('editStage')[0].setAttribute('data-id', stage.id);
                clone.getElementsByClassName('removeStage')[0].setAttribute('data-id', stage.id);

                $('.stagename').mouseover(function () {
                    $(this).find('.editContainer').removeClass('hidden');
                    $(this).find('.name').css('visibility', 'hidden');
                });

                $('.stagename').mouseout(function () {
                    $(this).find('.editContainer').addClass('hidden');
                    $(this).find('.name').css('visibility', 'visible');
                });

                $('.editStage').click(function () {
                    $('#btnUpdateStage').data('id', $(this).data('id'));
                    editStage($(this).data('id'));
                });

                $('.removeStage').click(function () {
                    removeStage($(this).data('id'));
                });
            }

            document.getElementById('stage_name').value = '';
            document.getElementById('stage_probability').value = '';
            document.getElementById('stages').appendChild(clone);
        }
    };

    var deal = {
        create: function() {

            document.getElementById('frmAddDeal').reset();

            var sel = document.getElementById('deal_contact_id');

            for (var i = sel.options.length; i >= 0; i--) {
                if (sel.options[i]) {
                    sel.removeChild(sel.options[i]);
                }
            }

            $('.stage_select div').removeClass('active');
            
            $("#dealModal").on('shown.bs.modal', function() {
                SI.Guides.restart();
            });

            $("#dealModal").on('hidden.bs.modal', function(){
                SI.Guides.restart();
            });

            $('#dealModal').modal('show');

        },
        add: function () {

            if (this.validate() === false) {
                return;
            }

            var data = {
                'userProfileId': document.getElementById('deal_contact_id').value,
                'organisationId': document.getElementById('deal_organisation_id').value,
                'pipelineId': document.getElementById('deal_pipeline_id').value,
                'stageId': document.getElementById('deal_stage_id').value,
                'ownerId': document.getElementById('deal_owner_id').value,
                'name': document.getElementById('deal_name').value,
                'value': document.getElementById('deal_value').value,
                'currency': document.getElementById('deal_currency').value,
                'expectedCloseDate': document.getElementById('deal_close_date').value
            };

            $.ajax({
                url: Routing.generate('restDealAdd'),
                type: 'POST',
                data: jQuery.param(data),
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                success: function (response, status, xhr) {

                    var listId, pathToPage;

                    if (!document.getElementById('stage_' + response.stage.id)) {
                        $('#dealModal').modal('hide');
                        return;
                    }

                    listId = 'stage_' + document.getElementById('deal_stage_id').value;
                    pathToPage = window.location.pathname;
                    $('#dealModal').modal('hide');

                    $('#' + listId).load(pathToPage + " #" + listId + " > *", function() {
                        $('#dealModal').modal('hide');
                        attachListeners();
                    });
                },
                error: function (response, status) {

                    if (status === 'error') {

                        var list = document.getElementById('deal_add_errors');
                        list.innerHTML = '';
                        for (i in response.responseJSON) {
                            list.innerHTML += response.responseJSON[i] + '<br>';
                        }

                        $('#deal_add_errors').removeClass('hidden');
                    }
                }
            });
        },

        // button is a jquery object
        edit: function(button) {
            var selectedOrganisation = null;

            document.getElementById('deal_organisation_id').value = button.data('organisation-id');
            document.getElementById('deal_contact_id').value = button.data('user-profile-id');
            document.getElementById('deal_stage_id').value = button.data('stage-id');
            document.getElementById('deal_name').value = button.data('name');
            document.getElementById('deal_value').value = button.data('value');
            document.getElementById('deal_currency').value = button.data('currency');
            document.getElementById('deal_pipeline_id').value = button.data('pipeline-id');
            document.getElementById('close_date').value = button.data('close-date');

            for (var i in SI.Pipeline.organisations) {
                if (SI.Pipeline.organisations[i].id === button.data('organisation-id')) {
                    selectedOrganisation = SI.Pipeline.organisations[i];
                    document.getElementById('deal_organisation').value = SI.Pipeline.organisations[i].name;
                    break;
                }
            }

            var selSontacts = document.getElementById('deal_contact_id');
            selSontacts.innerHTML = '';

            if (selectedOrganisation !== null) {
                for (var i in selectedOrganisation.contacts) {
                    var option = document.createElement('option');
                    option.value = selectedOrganisation.contacts[i][1];
                    option.innerHTML = selectedOrganisation.contacts[i][0];
                    option.selected = selectedOrganisation.contacts[i][1] === button.data('user-profile-id') ? true : false;
                    selSontacts.appendChild(option);
                }
            }

            SI.Pipeline.deal.stage.active($('div[data-pipeline-stage-id="' + button.data('stage-id') + '"]').get(0));

            $('#header_deal_add').addClass('hidden');
            $('#header_deal_edit').removeClass('hidden');
            $('#btnDealRemove').removeClass('hidden');
            $('#btnSaveDeal').removeClass('hidden');
            $('#btnAddDeal').addClass('hidden');

            $('#btnSaveDeal').data('id', button.data('id'));
            $('.btnRemoveDeal').data('id', button.data('id'));
            //var m = moment();
            //$('#close_date').calentim('gotoDate', m('2019-03-20'));
            var instance = $("#close_date").data("calentim");

            $('#close_date').data('iso', button.data('close-date'));
            $('#close_date').html(button.data('close-date'));
            $('#dealModal').modal('show');
        },

        save: function() {
            if (this.validate() === false) {
                return;
            }

            var data = {
                'id': $('#btnSaveDeal').data('id'),
                'userProfileId': document.getElementById('deal_contact_id').value,
                'organisationId': document.getElementById('deal_organisation_id').value,
                'pipelineId': document.getElementById('deal_pipeline_id').value,
                'stageId': document.getElementById('deal_stage_id').value,
                'ownerId': document.getElementById('deal_owner_id').value,
                'name': document.getElementById('deal_name').value,
                'value': document.getElementById('deal_value').value,
                'currency': document.getElementById('deal_currency').value,
                'expectedCloseDate': document.getElementById('deal_close_date').value
            };

            $.ajax({
                url: Routing.generate('restDealSave', {'id': $('#btnSaveDeal').data('id')}),
                type: 'PUT',
                data: jQuery.param(data),
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                success: function (response, status, xhr) {

                    var listId, pathToPage;

                    if (!document.getElementById('stage_' + data.stageId)) {
                        $('#dealModal').modal('hide');
                        return;
                    }

                    listId = 'stage_' + document.getElementById('deal_stage_id').value;
                    pathToPage = window.location.pathname;
                    $('#dealModal').modal('hide');

                    $('#' + listId).load(pathToPage + " #" + listId + " > *", function() {
                        $('#dealModal').modal('hide');
                        attachListeners();
                    });
                },
                error: function (response, status) {

                    if (status === 'error') {

                        var list = document.getElementById('deal_add_errors');
                        list.innerHTML = '';
                        for (var i in response.responseJSON) {
                            list.innerHTML += response.responseJSON[i] + '<br>';
                        }

                        $('#deal_add_errors').removeClass('hidden');
                    }
                }
            });
        },

        validate: function() {

            var label, errors, frm, fields, labelId;

            errors = 0;
            frm = document.getElementById('frmAddDeal');
            fields = Array.from(frm.getElementsByTagName('input'));
            fields = fields.concat(Array.from(frm.getElementsByTagName('select')));

            $('#deal_add_errors').addClass('hidden');

            for (var i = 0; i < fields.length; i++) {

                labelId = fields[i].hasAttribute('data-label') ? fields[i].getAttribute('data-label') : 'lbl_' + fields[i].id;

                if (!fields[i].hasAttribute('required')) {
                    continue;
                }

                if (!document.getElementById(labelId)) {
                    continue;
                }

                label = document.getElementById(labelId);
                label.style.color = '#676a6c';

                if (fields[i].value.trim() === '') {
                    label.style.color = '#ff0000';
                    errors++;
                }
            }

            return errors === 0;
        },

        // keep track of open swal modals
        openAlerts: 0,

        reorderProgress: false,

        reorderQueue: [],

        // deal is dragged to other column (2 ajax calls) or
        // position changed in same column (1 ajax call)
        reorder: function(event, ui) {

            var stageId = event.target.id.replace('stage_', '');
            var previousStageId = stageId;
            if (ui.sender !== null && ui.sender.length > 0) {
                previousStageId = ui.sender[0].id.replace('stage_', '');
            }

            var data = {
                dealId: ui.item[0].id,
                position: ui.item.index(),
                previousStage: previousStageId
            };

            $.ajax({
                url: Routing.generate('restDealReorder', {'id': stageId}),
                type: 'PUT',
                data: jQuery.param(data),
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                success: function (response, status, xhr) {

                }
            });
        },

        /**
         * @deprecated
         */
        processQueue: function() {

            if (SI.Pipeline.deal.reorderQueue.length === 0) {
                SI.Pipeline.deal.openAlerts = 0;
                swal.close();
                return;
            }

            var event = SI.Pipeline.deal.reorderQueue[0].event;
            var stageId = event.target.id.replace('stage_', '');
            var data = {
                'stageId': stageId,
                'deals': $('#' + event.target.id).sortable( "toArray" )
            };

            if (SI.Pipeline.deal.openAlerts === 0) {
                swal({
                    title: Translator.trans("alert_moment_title"),
                    text: Translator.trans("alert_deal_update_title"),
                    type: "info",
                    showLoaderOnConfirm: true,
                    showConfirmButton: false,
                    showCancelButton: false,
                    allowOutsideClick: false
                });
            }

            SI.Pipeline.deal.openAlerts++;

            if (SI.Pipeline.deal.reorderProgress === false) {

                SI.Pipeline.deal.reorderProgress = true;

                $.ajax({
                    url: Routing.generate('restDealReorder', {'id': stageId}),
                    type: 'PUT',
                    data: jQuery.param(data),
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    success: function (response, status, xhr) {
                        SI.Pipeline.deal.reorderQueue.shift();
                        SI.Pipeline.deal.reorderProgress = false;
                        SI.Pipeline.deal.processQueue();
                    }
                });
            }
        },

        finalize: function(dealId, status, reason) {

            swal({
                title: Translator.trans("alert_moment_title"),
                text: Translator.trans("alert_deal_update_title"),
                type: "info",
                showLoaderOnConfirm: true,
                showConfirmButton: false,
                showCancelButton: false,
                allowOutsideClick: false
            });

            var data = {
                'status': status === 'dealwon' ? 'won' : 'lost',
                'reason': reason
            };

            $.ajax({
                url: Routing.generate('restDealFinalize', {'id': dealId}),
                type: 'PUT',
                data: jQuery.param(data),
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                success: function (response, status, xhr) {
                    swal.close();
                }
            });
        },

        // btn is a jquery object $(this)
        remove: function(btn) {

            $('#dealModal').modal('hide');

            var dealId = btn.data('id');

            swal({
                title: Translator.trans("alert_del_title"),
                text: Translator.trans("alert_del_deal_text"),
                type: "warning",
                showCancelButton: true,
                cancelButtonText: Translator.trans("form_button_cancel"),
                confirmButtonColor: "#ed5565",
                confirmButtonText: Translator.trans("alert_del_confirm"),
                showLoaderOnConfirm: true,
                preConfirm: function(deleteFunction) {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: Routing.generate('restDealRemove', {'id': dealId }),
                            type: 'DELETE',
                            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                            success: function (response, status, xhr) {
                                var c = document.getElementById(dealId);
                                var p = c.parentNode;
                                p.removeChild(c);
                                resolve();
                            }
                        });
                    });
                },
            });
        },

        stage: {
            active: function(node) {
                document.getElementById('deal_stage_id').value = node.getAttribute('data-id');

                $('.stage_select .stage').removeClass('active');
                var stages = node.parentNode.getElementsByClassName('stage');

                for (var i in stages) {
                    $(stages[i]).addClass('active');

                    if (node.getAttribute('data-id') === $(stages[i]).data('id')) {
                        return;
                    }
                }
            },
        },

        pipeline: {

            // if user selected a pipeline from the add deal modal
            // hide all stage containers and show only stages connected
            // to selected pipeline
            activate: function(select) {
                $('.stage_select').addClass('hidden');
                $('div[data-pipeline-id="'+ select.value +'"]').removeClass('hidden');
            },

            // reload window with pipeline id
            select: function(select) {
                document.location = Routing.generate('pipelineOverview', {'id': select.value});
            }
        }
    };

    var typeahead = {

        // bootstrap 3 function
        // loads organistions from controller
        init: function(className, organisations) {
            $("." + className).typeahead({
                source: organisations,
                afterSelect: function(organisation) {

                    // set organisation id
                    document.getElementById('deal_organisation_id').value = organisation.id;

                    // clear select list
                    var contactDropdown = document.getElementById('deal_contact_id');
                    contactDropdown.innerHTML = '';

                    // add contacts
                    for (var e = 0; e < organisation.contacts.length; e++) {

                        var option = document.createElement('option');
                        option.value = organisation.contacts[e][1];
                        option.innerHTML = organisation.contacts[e][0];
                        contactDropdown.appendChild(option);
                    }
                }
            });
        }
    };

    // expose to public
    return {
        rearrange: rearrange,
        stage: stage,
        deal: deal,
        typeahead: typeahead
    };
}();