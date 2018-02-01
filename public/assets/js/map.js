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
			$('#worldmap').html('');
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
                        var  label_html= '</br>'+
                            '<b>'+data.lang_labels["calls"]+' </b>'+data.CountryChart[code].CallCount+'</br>'+
                            '<b>'+data.lang_labels["cost"]+' </b>'+data.CountryChart[code].TotalCost+'</br>'+
                            '<b>'+data.lang_labels["minutes"]+' </b>'+data.CountryChart[code].TotalMinutes+'</br>'+
                            '<b>'+data.lang_labels["acd"]+' </b>'+data.CountryChart[code].ACD+'</br>'+
                            '<b>'+data.lang_labels["asr"]+' </b>'+data.CountryChart[code].ASR+'%';
                            if(customer_login == 0) {
                                label_html+='</br><b>'+data.lang_labels["totalmargin"]+' </b>' + data.CountryChart[code].TotalMargin;
                                label_html+='</br><b>'+data.lang_labels["margin"]+' </b>' + data.CountryChart[code].MarginPercentage+'%';
                            }

                        el.html(el.html() + label_html );
                    }
                },
                onRegionClick: function(e,code){
                    if(data.CountryChart[code]) {
                        var submit_data_new = {};
                        submit_data_new = jQuery.extend({}, submit_data);
                        submit_data_new.CountryID = data.CountryChart[code].CountryID;
                        submit_data_new.chart_type = 'prefix';
						$('#modal-map h4 span').html(' ( '+data.CountryChart[code].Country+' ) ');
                        $('#modal-map').modal('show');
                        loadTable('#map_destination_table',submit_data_new.pageSize,submit_data_new)
                    }
                }
            });
            map.series.regions[0].setValues(data.CountryColor);
        }
    });
}