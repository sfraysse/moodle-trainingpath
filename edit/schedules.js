
require(['jquery', 'jqueryui'], function($, jqui) {

    // JQuery is available via $
    // JQuery UI is available via $.ui
    
	$(function() {

        // ----------------------------------- Load and display schedules ------------------------------------
    
    
        trainingpathLoadSchedules();
    
        function trainingpathLoadSchedules() {
            var url = $('#trainingpath-cards').attr('data-url');
            $.ajax({
                type: 'get',
                url: url,
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateSchedules(response);
                }
            });
        }
        
        function trainingpathUpdateSchedules(data) {
            $('#trainingpath-cards').empty();
            if (data.schedules.length == 0) {
                $('#trainingpath-cards').append('<p>'+data.lang.empty+'</p>');
            } else {
                for (i in data.schedules) {
                    var schedule = data.schedules[i];
                    var descr = schedule.description;
                    //if (!descr) descr = '<p class="card-text">'+data.lang.no_description+'</p>';
                    var html = '<div class="trainingpath-card-wrapper" data-id="'+schedule.id+'">';
                    html += '   <div class="card trainingpath-card">';
                    html += '       <div class="card-header with-tools">';
                    html += '           <div class="card-tools">';
                    
                    
                    // SF2017 - Icons
                    /*
                    html += '               <img src="'+data.icon.dragdrop+'" class="dragdrop" style="cursor:move;">';
                    html += '               <a href="'+data.url.edit+schedule.group_id+'"><img src="'+data.icon.edit+'" title="'+data.lang.edit+'"></a>';
                    if (data.url.certificates != undefined)
                        html += '           <a href="'+data.url.certificates+schedule.group_id+'"><img src="'+data.icon.certificates+'" title="'+data.lang.certificates+'"></a>';
                    if (data.url.batches != undefined)
                        html += '           <a href="'+data.url.batches+schedule.group_id+'"><img src="'+data.icon.batches+'" title="'+data.lang.batches+'"></a>';
                    html += '               <img src="'+data.icon.delete+'" title="'+data.lang.delete+'" class="delete">';
                    */
                    html += '               <span class="dragdrop">'+data.icon.dragdrop+'</span>';
                    html += '               <a href="'+data.url.edit+schedule.group_id+'">'+data.icon.edit+'</a>';
                    if (data.url.batches != undefined)
                        html += '               <a href="'+data.url.batches+schedule.group_id+'">'+data.icon.batches+'</a>';
                    html += '               <span class="delete">'+data.icon.delete+'</span>';

                    
                    html += '           </div>';
                    html += '           <h4 class="card-title">'+schedule.title+'</h4>';
                    html += '       </div>';
                    html += '       <div class="card-block">';
                    html +=             descr;
					html += '           <div class="trainingpath-card-space"></div>';
                    html += '       </div>';
                    html += '   </div>';
                    html += '   </div>';
                    $('#trainingpath-cards').append(html);
                }
            }
        }
        
        
        // ----------------------------------- Items manipulation ------------------------------------
        
        
        function trainingpathDeleteItem(id, url) {
            $.ajax({
                type: 'get',
                url: url+'&action=delete&id='+id,
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateSchedules(response);
                }
            });
        }
        
        function trainingpathReorderItems(ids, url) {
            $.ajax({
                type: 'get',
                url: url+'&action=reorder&ids='+ids.join(),
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateSchedules(response);
                }
            });
        }
        
        
        // ----------------------------------- Events ------------------------------------
        
        
        $('#trainingpath-cards').on('click', 'span.delete', function() {
            var scheduleId = $(this).closest('.trainingpath-card-wrapper').attr('data-id');
            var url = $(this).closest('#trainingpath-cards').attr('data-url');
            $('#trainingpath-cards-confirm .btn.confirm').off('click');
            $('#trainingpath-cards-confirm .btn.confirm').click(function() {
                $('#trainingpath-cards-confirm').modal('hide');
                trainingpathDeleteItem(scheduleId, url);
            });
            $('#trainingpath-cards-confirm').modal('show');
        });

        
        $('#trainingpath-cards').sortable({
            handle: '.card-header span.dragdrop',
            stop: function(event, ui) {
                var url = $(this).closest('#trainingpath-cards').attr('data-url');
                var ids = [];
                $('.trainingpath-card-wrapper').each(function(index) {
                    ids.push($(this).attr('data-id'));
                });
                trainingpathReorderItems(ids, url);
            }
        });

	});

    
});	
