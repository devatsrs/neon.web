function getWorldMap(submit_data){
    $('#worldmap').html('');
    loading(".world-map-chart",1);
    $.ajax({
        type: 'POST',
        url: submit_data.map_url,
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading(".world-map-chart",0);
            map = new jvm.Map({
                map: 'world_mill_en',
                container: $('#worldmap'),
                backgroundColor: '#ADD8E6',
                series: {
                    regions: [{
                        attribute: 'fill'}]
                },
                onRegionTipShow: function(e, el, code){
                    if(data.CountryChart[code]) {
                        var label_html = '</br>'+
                            '<b>Calls: </b>'+data.CountryChart[code].CallCount+'</br>'+
                            '<b>Cost: </b>'+data.CountryChart[code].TotalCost+'</br>'+
                            '<b>Minutes: </b>'+data.CountryChart[code].TotalMinutes+'</br>'+
                            '<b>ACD: </b>'+data.CountryChart[code].ACD+'</br>'+
                            '<b>ASR: </b>'+data.CountryChart[code].ASR+'%';

                        el.html(el.html() + label_html );
                    }
                },
                onRegionClick: function(e,code){
                    if(data.CountryChart[code]) {
                        var submit_data_new = {};
                        submit_data_new = jQuery.extend({}, submit_data);
                        submit_data_new.CountryID = data.CountryChart[code].CountryID;
                        submit_data_new.chart_type = 'prefix';
						$('#modal-map h4').html('Traffic By Prefix ( '+data.CountryChart[code].Country+' ) ');
                        $('#modal-map').modal('show');
                        loadTable('#map_destination_table',submit_data_new.pageSize,submit_data_new)
                    }
                }
            });
            map.series.regions[0].setValues(data.CountryColor);
        }
    });
}