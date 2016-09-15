$('.template_edit').on( "click",function(e){
    e.preventDefault();
    click_id = $(this).attr('id');
    $('.'+click_id+'_txt').addClass('hidden');
    $('.'+click_id+'_select').removeClass('hidden');
    return false;
});

$('#notification_setting').submit(function(e){
    e.preventDefault();
    submit_ajax(baseurl+'/notification/store_settings',$('#notification_setting').serialize());
    return false;
});
$('#save_notification').on("click",function(e){
    e.preventDefault();
    $('#save_notification').button('loading');
    $('#notification_setting').trigger('submit');
    return false;
});

$('#select-all').click(function(){
    $('.multi-select').multiSelect('select_all');
    return false;
});
$('#deselect-all').click(function(){
    $('.multi-select').multiSelect('deselect_all');
    return false;
});
$('.searchable').multiSelect({
    selectableHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='try \"xyz\"'>",
    selectionHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='try \"xyz\"'>",
    afterInit: function(ms){
        var that = this,
            $selectableSearch = that.$selectableUl.prev(),
            $selectionSearch = that.$selectionUl.prev(),
            selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
            selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

        that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
            .on('keydown', function(e){
                if (e.which === 40){
                    that.$selectableUl.focus();
                    return false;
                }
            });

        that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
            .on('keydown', function(e){
                if (e.which == 40){
                    that.$selectionUl.focus();
                    return false;
                }
            });
    },
    afterSelect: function(){
        this.qs1.cache();
        this.qs2.cache();
    },
    afterDeselect: function(){
        this.qs1.cache();
        this.qs2.cache();
    }
});


function populateInterval(jobtype,fromid){

    //console.log("in populateJonInterval ");
    $("#"+fromid+" [name='Setting[JobInterval]']").addClass('visible');
    var selectBox = $("#"+fromid+" [name='Setting[JobInterval]']").selectBoxIt().data("selectBox-selectBoxIt");
    var selectBoxStartDay = $("#"+fromid+" [name='Setting[JobStartDay]']").selectBoxIt().data("selectBox-selectBoxIt");
    $("#"+fromid+" .JobStartDay").hide();
    var starttime = $("#"+fromid+" .starttime");
    if(selectBox){
        selectBox.remove();
        // console.log("jobtype" + jobtype);
        if(jobtype == 'HOUR'){
            for(var i=1;i<'24';i++){
                selectBox.add({ value: i, text: i+" Hour"})
            }
            starttime.show();
        }else if(jobtype == 'MINUTE'){
            for(var i=1;i<60;i++){
                selectBox.add({ value: i, text: i+" Minute"})
            }
            starttime.hide();
            starttime.val('');
        }else if(jobtype == 'DAILY'){
            for(var i=1;i<'32';i++){
                selectBox.add({ value: i, text: i+" Day"})
            }
            //console.log("jobtype" + jobtype);
            starttime.show();
        }else if(jobtype == 'MONTHLY'){
            for(var i=1;i<13;i++){
                selectBox.add({ value: i, text: i+" Month"})
            }
            for(var i=1;i<'32';i++){
                selectBoxStartDay.add({ value: i, text: i+" Day"})
            }
            $("#"+fromid+" .JobStartDay").show();
            starttime.show();
        }
    }
}