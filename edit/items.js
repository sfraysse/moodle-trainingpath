
require(['jquery', 'jqueryui'], function($, jqui) {

    // JQuery is available via $
    // JQuery UI is available via $.ui
    
	$(function() {

        // ----------------------------------- Load and display items ------------------------------------
    
    
        trainingpathLoadItems();
    
        function trainingpathLoadItems() {
            var url = $('#trainingpath-cards').attr('data-url');
            $.ajax({
                type: 'get',
                url: url,
                error: function(xhr, status, errorThrown) {
                    console.log('e-ATPL Ajax Error '.xhr.status);
                },
                success: function(response) {
                    trainingpathUpdateItems(response);
                }
            });
        }
        
        function trainingpathUpdateItems(data) {
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
                    if (data.url.children != undefined)
                        html += '           <a href="'+data.url.children+item.id+'"><img src="'+data.icon.children+'" title="'+data.lang.children+'"></a>';
                    html += '               <img src="'+data.icon.delete+'" title="'+data.lang.delete+'" class="delete">';
                    */
                    html += '               <span class="dragdrop">'+data.icon.dragdrop+'</span>';
                    html += '               <a href="'+data.url.edit+item.id+'">'+data.icon.edit+'</a>';
                    if (data.url.children != undefined)
                        html += '               <a href="'+data.url.children+item.id+'">'+data.icon.children+'</a>';
                    html += '               <span class="delete">'+data.icon.delete+'</span>';

                    
                    html += '           </div>';
                    if (item.code != '') html += '<h4 class="card-title">['+item.code+'] '+item.title+'</h4>';
                    else html += '      <h4 class="card-title">'+item.title+'</h4>';
                    html += '       </div>';
                    html += '       <div class="card-block">';
                    html +=             descr;
                    /*
                    if (data.url.children != undefined)
                        html += '       <a href="'+data.url.children+item.id+'" class="btn btn-primary btn-sm" role="button">'+data.lang.children+'</a>';
                    */
                    if (data.lang.open != undefined)
                        html += '       <div class="trainingpath-commands"><a href="'+item.open.url+'" target="'+item.open.target+'" class="btn btn-sm btn-secondary" role="button">'+data.lang.open+'</a></div>';
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
                    trainingpathUpdateItems(response);
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
                    trainingpathUpdateItems(response);
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
                trainingpathDeleteItem(itemId, url);
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
