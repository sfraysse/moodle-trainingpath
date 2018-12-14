
require(['jquery', 'jqueryui'], function($, jqui) {

    // JQuery is available via $
    // JQuery UI is available via $.ui
    
	$(function() {

        // ----------------------------------- Load and display items ------------------------------------
    
    
        trainingpathLoadCalendars();
    
        function trainingpathLoadCalendars() {
            var url = $('#trainingpath-cards').attr('data-url');
            $.ajax({
                type: 'get',
                url: url,
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateCalendars(response);
                }
            });
        }
        
        function trainingpathUpdateCalendars(data) {
            $('#trainingpath-cards').empty();
            if (data.items.length == 0) {
                $('#trainingpath-cards').append('<p>'+data.lang.empty+'</p>');
            } else {
                for (i in data.items) {
                    var item = data.items[i];
                    var descr = item.description;
                    //if (!descr) descr = '<p class="card-text">'+data.lang.no_description+'</p>';
                    var html = '<div class="trainingpath-card-wrapper" data-id="'+item.id+'">';
                    html += '   <div class="card trainingpath-card">';
                    html += '       <div class="card-header with-tools">';
                    html += '           <div class="card-tools">';
                    
                    // SF2017 - Icons
                    /*
                    html += '               <img src="'+data.icon.dragdrop+'" class="dragdrop" style="cursor:move;">';
                    html += '               <a href="'+data.url.edit+item.id+'"><img src="'+data.icon.edit+'" title="'+data.lang.edit+'"></a>';
                    html += '               <img src="'+data.icon.delete+'" title="'+data.lang.delete+'" class="delete">';
                    */
                    html += '               <span class="dragdrop">'+data.icon.dragdrop+'</span>';
                    html += '               <a href="'+data.url.edit+item.id+'">'+data.icon.edit+'</a>';
                    html += '               <span class="delete">'+data.icon.delete+'</span>';
                    
                    html += '           </div>';
                    if (item.code != '') html += '<h4 class="card-title">'+item.title+'</h4>';
                    else html += '      <h4 class="card-title">'+item.title+'</h4>';
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
        
        
        // ----------------------------------- Calendars manipulation ------------------------------------
        
        
        function trainingpathDeleteCalendar(id, url) {
            $.ajax({
                type: 'get',
                url: url+'&action=delete&id='+id,
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateCalendars(response);
                }
            });
        }
        
        function trainingpathReorderCalendars(ids, url) {
            $.ajax({
                type: 'get',
                url: url+'&action=reorder&ids='+ids.join(),
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateCalendars(response);
                }
            });
        }
        
        
        // ----------------------------------- Events ------------------------------------
        
        
        $('#trainingpath-cards').on('click', 'span.delete', function() {
            var itemId = $(this).closest('.trainingpath-card-wrapper').attr('data-id');
            var url = $(this).closest('#trainingpath-cards').attr('data-url');
            $('#trainingpath-cards-confirm .btn.confirm').off('click');
            $('#trainingpath-cards-confirm .btn.confirm').click(function() {
                $('#trainingpath-cards-confirm').modal('hide');
                trainingpathDeleteCalendar(itemId, url);
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
                trainingpathReorderCalendars(ids, url);
            }
        });

	});

    
});	
