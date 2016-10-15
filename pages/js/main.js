/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

//INIT
    var dbtabledata = null;
    var csvtabledata = null;
    var select_html_db = null; //html-code selectbox config assoc.

    var alert_msg_1 = "Wählen Sie die entsprechenden Spalten der Datenbanktabelle!";
    var alert_msg_2 = "Wählen Sie die Spalten der Datenbanktabelle exportiert werden und zeigen neben dem Namen, den Sie die csv zuordnen möchten!";

    var direction_msg_1 = "Import von CSV in die Datenbank";
    var direction_msg_2 = "Export von Datenbank in die CSV-Datei";

    $(".dbtocsv").hide();

    /////////////EVENTS HANDLE//////////////////////////

    //It does everything as it was immediately after login
    $("#reinit-btn").click(function () {
        JQUERY4U.sendToServer("reinitbtn.php"); //reset all settings parameters saved in session 
        location.reload();
    });

    $("#select-mysql-table").change(function () {
        dbtabledata = null;
        saveConf();
        show_db_preview();
        if ($(this).val() != "" && ($("#uploaded-file-name").html() != "" || $("input[name='radio_inp_exp']:checked").val() === "db_csv")) {
            show_config_table();
        }
    });

    $("#autoconf").click(function () {
        if ($(this).is(":checked")) {
            $(".csvconf").prop('disabled', 'disabled');
             update();
        } else {
            $(".csvconf").prop('disabled', false);
        }

    });


    $("#enclosure, #separator, #charset").change(function () {
        update();
    });


    $('#div_radio_btn').change(function () {
        checkbtntest()
    });

    $('input:radio[name=radiopreview]').click(function () {
        if ($(this).val() == 'csv') {
            show_csv_preview();
        } else {
            show_db_preview();
        }
    });
    
     $("#garbage").click(function () {
        if ($("#select-mysql-table").val() == "") {
            alert("Keine Datenbanktabelle ausgewählten!");
        } else if (confirm("Die Datenbanktabelle: " + $("#select-mysql-table").val() + " wird entleert! Vorgehen?")) {
            $.post("deleteondb.php", {"tablename": $("#select-mysql-table").val()}, function (data, status) {
                //alert("Data: " + data + "\nStatus: " + status);
                dbtabledata = null;
                show_db_preview();
            });
        }
    });

    $(document).on('click', '#import-btn', function () {

        var arr = getAssocCsvDb();
        if (arr["assoc"].length != 0) {
            if (confirm("Achtung! Es wird in die Datenbank geschrieben werden. Vorgehen?")) {
                var issue = JQUERY4U.sendToServer("save_on_db.php", JSON.stringify(arr));
                if (issue[0] == 1) { //msg from DBMS
                    alert("Fehler! Das DBMS antwortet mit der Nachricht: \"" + issue + "\"");
                } else if (issue[0] == 0) { //msg from my function
                    if (issue[1] == true) {
                        dbtabledata = null;
                        show_db_preview();
                        alert("Erfolg!")
                    } else {
                        alert("Fehler! " + issue[1]);
                    }
                }
            }
        } else {
            alert(alert_msg_1)
        }

    });


    $(document).on('click', '#export-btn', function () {
        var arr = getAssocCsvDb();
        if (arr["assoc"].length != 0) {
            var issue = JQUERY4U.sendToServer("create_csv.php", JSON.stringify(arr));
            // alert(issue)
            if (issue == "success") {
                document.location = "wrapper.php";
            } else {
                alert("Fehler!");
            }
        } else {
            alert(alert_msg_2)
        }
    });

    ///////////////////////////////////////////////////



    ///////////////FUNCTIONS///////////////////////////


    function update() {
        csvtabledata = null;
        saveConf();
        if ($('input:radio[name=radiopreview]').val() == 'csv' && $("#uploaded-file-name span").html() != "") {
            show_csv_preview();
        }
    }

    function getAssocCsvDb() {

        var arr_assoc = Array();
        var arr_csv_options = Array();
        var index = 0;
        var csv_index = 0;

        $('.csv-column br').remove();

        $("#configuration-table tr select").each(function () {

            var selected_value = $(this).val();
            var csv_col = $(this).closest("tr").find(".csv-column").html();

            if (selected_value != "") {
                arr_assoc[index++] = {
                    "dbcolumnname": selected_value,
                    "csvindex": csv_index,
                    "csvcolumnname": csv_col
                }
            }
            csv_index++;
        });

        arr_csv_options = {
            "tablename": $("#select-mysql-table").val(),
            "filename": $("#uploaded-file-name span").html(),
            "separator": $("#separator").val(),
            "enclosure": $("#enclosure").val(),
            "charset": $("#charset").val()
        }

        var arr = {
            "csv_options": arr_csv_options,
            "assoc": arr_assoc
        };

        // alert(JSON.stringify(arr["assoc"]));

        return arr;

    }



    function show_msg(msg) {
        $("#info-msg").html(msg).fadeOut(5000);
    }


    //This function save the settings in the session. It does not save the actual values but only indexing. PHP gets the actual values using the array-mapping.    
    function saveConf() {
        var pos = $("#select-mysql-table option:selected").attr("data-id");
        var tablename = pos == "0" ? "" : $("#select-mysql-table").val();
        JQUERY4U.sendToServer("session.php", JSON.stringify({
            "pos": pos,
            "tablename": tablename,
            "fnup": $("#uploaded-file-name span").html(),
            "chset": $("#charset").val(),
            "sep": $("#separator").val(),
            "encl": $("#enclosure").val(),
            "autoconf": $("#autoconf").is(":checked")
        }));
    }

    function show_db_preview() {
        if (!dbtabledata) {
            dbtabledata = JQUERY4U.sendToServer("getcontenttable.php");
        }

        $('input:radio[name=radiopreview]').val(['db']); //set radiobutton for title

        $("#mytable").empty();

        $('#mytable').mytable({
            'tablearrayjs': dbtabledata,
            'numrowperpage': 10
        });

    }

    function show_csv_preview() {
        if (!csvtabledata) {
            csvtabledata = JQUERY4U.sendToServer("csvhandle.php")
        }
//alert(csvtabledata["info"]["sep"])

        $("#charset").val(csvtabledata["info"]["chset"]);
        $("#separator").val(csvtabledata["info"]["sep"]);

        $('input:radio[name=radiopreview]').val(['csv']); //set radiobutton for title

        $("#mytable").empty();

        $('#mytable').mytable({
            'tablearrayjs': csvtabledata,
            'numrowperpage': 10
        });
        //...
    }
    
    


    function getCodeForSelectboxDb() {
        var html = "<select class=\"db_assoc\" onchange=\"JQUERY4U.assocChange() \">\n" +
                "<option value=\"\">&nbsp;</option>";
        for (var i = 0; i < dbtabledata["tabheader"].length; i++) {
            html += "<option value=\"" + dbtabledata["tabheader"][i]["data"] + "\">" + dbtabledata["tabheader"][i]["data"] + "</option>\n";
        }

        html += "</select>";
        return html;
    }


    function show_config_table() {

        if ($("#select-mysql-table").val() != "") {  //no mysqltable selected or not csv uploaded  

            var html_header = "";
            var html_body = "";
            var html_footer = "";
            select_html_db = getCodeForSelectboxDb();

            if ($('input:radio[name=radio_inp_exp]:checked').val() == "csvtodb") {

                html_header = "<thead><tr><td colspan=3 id=\"direction-title\">" + direction_msg_1 + "<img src=\"../css/info.png\" title=\"" + alert_msg_1 + "\" alt=\"info\" class=\"info-icon\" onclick=\"alert(this.getAttribute('title'))\"></td></thead><tbody>";
                html_footer = "</tbody>\n<tfoot><tr><td colspan=3><button id=\"import-btn\">Import</button></td></tr></tfoot>";
                if ($("#uploaded-file-name span").html() == "") {
                    $("#configuration-table").html("");
                    return;  //not csv file uploaded
                }
                for (var i = 0; i < csvtabledata["tabheader"].length; i++) { //csv column
                    html_body += "<tr><td class=\"csv-column\">" + csvtabledata["tabheader"][i]["title"] + "</td><td>---></td><td>" + select_html_db + "</td><tr>\n";
                }

            } else {

                html_header = "<thead><tr><td colspan=3 id=\"direction-title\">" + direction_msg_2 + "<img src=\"../css/info.png\" title=\"" + alert_msg_2 + "\" alt=\"info\" class=\"info-icon\" onclick=\"alert(this.getAttribute('title'))\"></td></thead><tbody>";
                html_footer = "</tbody>\n<tfoot><tr><td colspan=3><button id=\"export-btn\">Export</button>\n</tr></td></tfoot>";
                for (var i = 0; i < dbtabledata["tabheader"].length; i++) {  //db column
                    html_body += "<tr class=\"exp\"><td>" + select_html_db + "</td><td>---></td><td contenteditable=\"true\" class=\"csv-column\" ></td></tr>";
                }
            }



            $("#configuration-table").html(html_header + html_body + html_footer);
            //  $(".table-title").css("visibility", "visible");
        } else {
            $("#configuration-table").html("");
            //  $(".table-title").css("visibility", "hidden");
        }
    }
    
    
  
    function checkbtntest() {
        if ($("input[name='radio_inp_exp']:checked").val() === "csvtodb") { //inport in db
            $(".csvtodb").show();
            $(".dbtocsv").hide();
            if ($("#uploaded-file-name span").html() == "") {
                show_msg("Es ist notwendig, um eine CSV-Datei zu laden!");
            } else {
                show_csv_preview();
                if ($("#select-mysql-table").val() == "") {
                    show_msg("Es ist notwendig, um eine DB-Table wählen!");
                } else {
                    show_config_table();
                }
            }

        } else { //export in csv    if db to csv
            //alert("export")
            $(".csvtodb").hide();
            $(".dbtocsv").show();
            if ($("#select-mysql-table").val() == "") {
                show_msg("Es ist notwendig, um eine DB-Table wählen!");
            } else {
                show_db_preview();
                show_config_table();
            }
        }
    }

////////////////// BLOCKS /////////////////////////////////////
    $("#fileuploader").uploadFile({
        url: "upload.php",
        maxFileSize: 16777216,
        sizeErrorStr: "<br /> Fehler! Es erlaubt max:",
        dragDrop: false,
        fileName: "myfile",
        returnType: "json",
        showDelete: false,
        showDownload: false,
        multiple: false,
        acceptFiles: "text/csv",
        uploadStr: "W&auml;len Sie der CSV-Dateiname<br /> zu laden",
        onSuccess: function (files, data, xhr, pd)
        {
            csvtabledata = null;
            $("#uploaded-file-name").find("span").html(data[0]);
            saveConf();
            show_csv_preview();
            if ($("#select-mysql-table").val() != "" || $("input[name='radio_inp_exp']:checked").val() === "db_csv") {
                show_config_table();
            }

        },
        onError: function (files, status, errMsg, pd)
        {
            //files: list of files
            //status: error status
            alert(errMsg)
        }
    });
//////////////////////////////////////////////////////////////



});//close $(document).ready...
