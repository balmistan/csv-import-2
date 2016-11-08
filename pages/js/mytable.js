/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function ($) {
    var obj = null;
    $.fn.mytable = function (options) {
// valori di default
        var config = {
            'tablearrayjs': Array(),
            'numrowperpage': 2,
            'serverside': false,
            'ajaxpage': '',
            'success': ''
        };
        if (options)
            $.extend(config, options);


        //  this.each(function () {
        obj = $(this);
        $(obj).data("tablearray", config["tablearrayjs"]);
        $(obj).data("tablepage", 1);
        $(obj).data("numrowperpage", config["numrowperpage"]);
        $(obj).data("serverside", config["serverside"]);
        $(obj).data("ajaxpage", config["ajaxpage"]);

        tabledraw();

        if (typeof config["success"] == 'function') {
            return this.each(function () {
                options.success(JSON.stringify($(obj).data("tablearray")));

            });
        }
        //  alert(hd+dt)
        //     });  //close this.each(function () {...

    }


    function tabledraw() {
        if ($(obj).data("serverside")) {
            confserverside();
        }
        tabledraw_clientside();
    }


    function confserverside() {

        var startrow = $(obj).data("tablepage") == 1 ? 0 : ($(obj).data("tablepage") - 1) * $(obj).data("numrowperpage");


        $.ajax({
            url: $(obj).data("ajaxpage"),
            type: 'POST',
            data: JSON.stringify({"start": startrow, "limit": $(obj).data("numrowperpage")}),
            async: false,
            success: function (data) {
                try {
                    $(obj).data("tablearray", JSON.parse(data));

                } catch (error) {
                    alert("ERROR!");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

    }



    function tabledraw_clientside() {
        var hd = "<thead>";
        var dt = "<tbody>";


        if ($(obj).data("tablearray")["tabheader"] !== undefined) {
            var numcol = $(obj).data("tablearray")["tabheader"].length;
            //create header

            hd += "<tr>\n";
            for (var i = 0; i < numcol; i++) {
                hd += "<th>" + $(obj).data("tablearray")["tabheader"][i]["title"] + "</th>\n";
            }
            hd += "</tr></thead>\n"

        }

        var totalnumrows = 0;
        var startrow = 0;
        var endrow = 0;
        if($(obj).data("serverside")){
            startrow = 0;
            endrow = $(obj).data("numrowperpage");
        }else{
         startrow = $(obj).data("tablepage") == 1 ? 0 : ($(obj).data("tablepage") - 1) * $(obj).data("numrowperpage");
         endrow = $(obj).data("tablepage") * $(obj).data("numrowperpage");
    }
            // create data content

        if (typeof (($(obj).data("tablearray")["tabcontent"][0])) !== 'undefined') {

            if (($(obj).data("tablearray")["numrows"]) !== 'undefined') {
                totalnumrows = $(obj).data("tablearray")["numrows"];
            } else {
                totalnumrows = $(obj).data("tablearray")["tabcontent"].length
            }
            //alert(totalnumrows)

            if (typeof ($(obj).data("tablearray")["tabheader"][0]["data"]) === 'undefined') {
                //Data from csv

                for (var i = startrow; i < endrow && i < $(obj).data("tablearray")["tabcontent"].length; i++) {
                    dt += "<tr>\n";
                    for (var index = 0; index < $(obj).data("tablearray")["tabcontent"][i].length; index++) {

                        dt += "<td>" + $(obj).data("tablearray")["tabcontent"][i][index] + "</td>\n";
                    }
                    dt += "</tr>";
                }//close extern for

            } else {//Data from queries
                for (var i = startrow; i < endrow && i < $(obj).data("tablearray")["tabcontent"].length; i++) {
                    dt += "<tr>\n";
                    for (var index in $(obj).data("tablearray")["tabheader"]) {
                        //alert(tablearray["tabheader"][index])
                        dt += "<td>" + $(obj).data("tablearray")["tabcontent"][i][$(obj).data("tablearray")["tabheader"][index]["data"]] + "</td>\n";
                    }
                    dt += "</tr>";
                }//close extern for
            }//close else
        }
        dt += "</tbody>";


        //create block button
        // alert(Math.floor(totalnumrows / config["numrowperpage"]) + "; "+ totalnumrows % config["numrowperpage"])
        //alert(totalnumrows % numrowperpage)
        var numtotbuttons = totalnumrows % $(obj).data("numrowperpage") > 0 ? Math.floor(totalnumrows / $(obj).data("numrowperpage")) + 1 : Math.floor(totalnumrows / $(obj).data("numrowperpage"))

        var codebtn = "";

        if ($(obj).data("tablepage") > 1) {
            codebtn += "<button><</button>";
        }

        if ($(obj).data("tablepage") == 1) {
            codebtn += "<button class=\"cred\">1</button>";
        } else {
            codebtn += "<button>1</button>";
        }




        var cnt = 0;

        var start = parseInt($(obj).data("tablepage")) > 5 ? parseInt($(obj).data("tablepage")) - 3 : 2
        if (parseInt($(obj).data("tablepage")) > 5) {
            codebtn += "...";
        }
        for (var i = start; i <= numtotbuttons && cnt < 4; i++) {
            cnt++;
            if (i == $(obj).data("tablepage")) {
                codebtn += "<button class=\"cbold\">" + i + "</button>";
            } else {
                codebtn += "<button>" + i + "</button>";
            }
        } //close for

        if ($(obj).data("tablepage") < numtotbuttons) {
            codebtn += "...<button>></button>";
        }
        //create footer
        var tf = "";

        if (totalnumrows) {
            tf += "<tfoot><tr><td colspan=\"" + numcol + "\"><div id=\"buttons\">" + codebtn + "</div></td></tr>\n<tr><td colspan=\"" + numcol + "\">Gesamtergebnisse: " + totalnumrows + "</td></tr>\n</tfoot>";
        } else {
            tf += "<tfoot><tr><td colspan=\"" + numcol + "\">Die Tabelle ist leer!</td></tr></tfoot>";
        }

        $(obj).html(hd + dt + tf);
        return;
    }


    //events handle

    $(document).on("click", "#buttons button", function () {
//alert($(this).html())
        switch ($(this).html()) {
            case '<':
            case '&lt;':
                $(obj).data("tablepage", parseInt($(obj).data("tablepage")) - 1);
                break;
            case '>':
            case '&gt;':
                $(obj).data("tablepage", parseInt($(obj).data("tablepage")) + 1);
                // alert($(obj).data("tablepage"))
                break;
            default:
                $(obj).data("tablepage", $(this).html());
                break;
        }
        tabledraw();
    })



})(jQuery);

