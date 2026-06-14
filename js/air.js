$(document).ready(function () {
  if (window.history.replaceState) { // biar kalo refresh abis add, halaman ga ngirim ulang data POST
    window.history.replaceState(null, null, window.location.href);
  }
  // Kalau jquery tidak jalan, lakukan Hard Refresh (Ctrl+F5)
  uri = window.location.href;
  e = uri.split("=");
  console.log("URI: " + uri + " e[1]:" + e[1] + " e[2]:" + e[2]);

  // tombol garis tiga

  $("#sidebarToggle").click(function (e) {
    e.preventDefault();
    $("body").toggleClass("sb-sidenav-toggled");
  });

  // add user

  if (e[1] == "user") {
    $("#summary, #chart, #pilih_waktu, #user_add").hide();
    $("#user_list").show();

    if (e[1] == "user_edit&user") {
      $("#user_list").hide();
      $("#user_add").show();
      $("#user_form button").val("user_edit");
      $("#user_form input[name='yuser']").attr('readonly', true);
      $("#user_form").append("<input type='hidden' name='yuser' value='" + e[2] + "'>");
    }
    if ($("#btn_tambah_user").length === 0) {
      $(".datatable-dropdown").append("<button type=button class='btn btn-success float-start me-2' id='btn_tambah_user'><i class='fa-solid fa-user-plus'></i> User</button>");
    }
    $(document).on("click", "#btn_tambah_user", function () {
      $("#user_list").hide();
      $("#user_add").show();
      $("#user_form input, #user_form textarea, #user_form select").val('');
    });

    // menu tarif
  } else if (e[1] == "manajemen_tarif_air" || e[1] == "tarif_edit&id_tarif") {

    $("#summary, #chart, #user_add, #user_list, #tarif_add, #pilih_waktu, #chart").hide();
    $("#tarif_list").show();

    const tarifTable = document.getElementById('tarif_table');
    if (tarifTable) {
      new simpleDatatables.DataTable(tarifTable);
    }


    // add tarif
    if ($("#btn_tambah_tarif").length === 0) {
      $(".datatable-dropdown").append("<button type=button class='btn btn-success float-start me-2' id='btn_tambah_tarif'><i class='fa-solid fa-money-bill-wave'></i> Tarif</button>");
    }
    // kebal peluru tarif
    $(document).on("click", "#btn_tambah_tarif", function () {
      $("#tarif_list").hide();
      $("#tarif_add").show();

      $("#tarif_form input[type='text'], #tarif_form input[type='number']").val('');
      $("#tarif_form button[type='submit']").val('tarif_add');
      $("#tarif_form input[name='id_tarif']").removeAttr('readonly');
    });

    if (e[1] == "tarif_edit&id_tarif") {
      $("#tarif_list").hide();
      $("#tarif_add").show();
      $("#tarif_form button").val('tarif_edit');
      $("#tarif_form input[name='id_tarif']").attr('readonly', true);
      $("#tarif_form").append("<input type='hidden' name='id_tarif_lama' value='" + e[2] + "'>");
    }

  } else if (e[1] == "catat_edit_meter" || e[1] == "meter_edit&no" || e[1] == "pemakaian_warga") { // petugas

    $("#summary, #chart, #user_add, #user_list, #tarif_add, #meter_add, #tarif_list, #pilih_waktu, #chart").hide();
    $("#meter_list").show();

    const meterTable = document.getElementById('meter_table');
    if (meterTable) {
      new simpleDatatables.DataTable(meterTable);
    }

    // add meter
    if (typeof level_user !== 'undefined' && (level_user === "admin" || level_user === "petugas")) { //penghilang tombol add meter di bendahara
      if ($("#btn_tambah_meter").length === 0) {
        $(".datatable-dropdown").append("<button type=button class='btn btn-success float-start me-2' id='btn_tambah_meter'><i class='fa-solid fa-circle-plus'></i> Meter</button>");
      }
    }
    // gabisa ubah nama
    // SENSOR KLIK METER
    $(document).on("click", "#btn_tambah_meter", function () {
      $("#meter_list").hide();
      $("#meter_add").show();

      // 1. Kosongkan input teks, number, DAN SELECT (Dropdown)
      $("#meter_form input[type='text'], #meter_form input[type='number'], #meter_form select").val('');

      // 1b. Bersihkan juga warna biru otomatisasi JSON yang mungkin tertinggal
      $("#meter_awal").css("background-color", "");

      // 2. HAPUS input hidden 'no' yang mungkin tertinggal dari proses edit
      $("#meter_form input[name='no']").remove();

      // 3. Kembalikan teks tombol ke mode tambah
      $("#meter_form button[type='submit']").val('meter_add');
    });

    if (e[1] == "meter_edit&no") {
      $("#tarif_add, #tarif_list, #meter_list").hide();
      $("#meter_add").show();
      $("#meter_form").append("<input type='hidden' name='no' value='" + e[2] + "'>");
      $("#meter_form button").val('meter_edit');
      $("#meter_form input[name='no']").attr('readonly', true);

    }


  }
  else if (e[1] == "pantau_pemakaian") { // warga

    $("#summary, #chart, #pilih_waktu").hide();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list").hide();
    $("#warga_pemakaian_list").show();

    const wargaTable = document.getElementById('warga_table');
    if (wargaTable) {
      new simpleDatatables.DataTable(wargaTable);
    }
    // JARING PENGAMAN MENU BARU (Menghalangi Dashboard tembus ke Infografis & Tagihan)
  } else if (e[1] == "tagihan_warga" || e[1] == "tagihan_saya" || e[1] == "infografis_warga" || e[1] == "infografis_tagihan_warga") {
    $("#summary, #pilih_waktu").hide();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list, #warga_pemakaian_list").hide();

    // Jika menu infografis, munculkan grafiknya. Jika tagihan, sembunyikan grafik.
    if (e[1] == "tagihan_warga" || e[1] == "tagihan_saya") {
      $("#chart").hide();
    } else {
      $("#chart").show();
    }
    // dashboard 
  } else {
    // ==========================================
    // LOGIKA AJAX DASHBOARD ROLE-BASED
    // ==========================================
    // KUNCI MUTLAK: KITA SECARA AKTIF MEMUNCULKAN KEMBALI ELEMEN DASHBOARD!
    $("#summary, #chart, #pilih_waktu").show();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list, #warga_pemakaian_list").hide();
    $("#pilih_waktu select[name='pilih_waktu']").on("change", function () {
      var bln = $(this).val();

      if (bln == "") {

        $("#box1").text("orang");
        $("#box2").text("m³");
        $("#box3").text("orang");
        $("#box4").text("orang");


      } else {


        $.ajax({
          type: "post",
          url: "../assets/ajax.php",
          data: { p: "summary", t: bln },
          dataType: "json"
        })
          .done(function (d) {
            // d adalah array hasil kiriman json_encode dari ajax.php

            if (level_user === "admin" || level_user === "petugas") {
              $("#box1").text(d[0].jml_pelanggan + " Orang");
              $("#box2").text(d[1].total_pemakaian + " m³");
              $("#box3").text(d[2].tercatat + " Warga");
              $("#box4").text(d[3].belum_dicatat + " Warga");
            }
            else if (level_user === "bendahara") {
              $("#box1").text(d[0].jml_pelanggan + " Orang");
              $("#box2").text(d[1].total_pemasukan);
              $("#box3").text(d[2].lunas + " Warga");
              $("#box4").text(d[3].belum_bayar + " Warga");
            }
            else if (level_user === "warga") {
              $("#box1").text(d[0].info);
              $("#box2").text(d[1].info);
              $("#box3").text(d[2].info);
              $("#box4").text(d[3].info);
            }
          })
          .fail(function (xhr, status, error) {
            console.log("Error Terjadi!");
            console.log("Pesan Server:", xhr.responseText);
          });
      }

      $.ajax({
        type: "post",
        url: "../assets/ajax.php",
        data: { p: "chart_bar", y: user },
        dataType: "json"
      })
        .done(function (response) {
          console.log("Data Chart Berhasil Masuk:", response);
          sumbuX = response.filter((num, index) => index % 2 == 0);
          sumbuY = response.filter((num, index) => index % 2 != 0);
          // Set new default font family and font color to mimic Bootstrap's default styling
          Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
          Chart.defaults.global.defaultFontColor = '#292b2c';

          // Bar Chart Example
          var ctx = document.getElementById("myBarChart");
          var myLineChart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: sumbuX,
              datasets: [{
                label: "Tagihan (Rp)",
                backgroundColor: "rgba(2,117,216,1)",
                borderColor: "rgba(2,117,216,1)",
                data: sumbuY,
              }],
            },
            options: {
              scales: {
                xAxes: [{
                  time: {
                    unit: 'month'
                  },
                  gridLines: {
                    display: false
                  },
                  ticks: {
                    maxTicksLimit: 6
                  }
                }],
                yAxes: [{
                  ticks: {
                    min: 0,
                    max: 300,
                    maxTicksLimit: 5
                  },
                  gridLines: {
                    display: true
                  }
                }],
              },
              legend: {
                display: false
              }
            }
          });
        });

      $.ajax({
        type: "post",
        url: "../assets/ajax.php",
        data: { p: "chart_line", y: user },
        dataType: "json"
      })
        .done(function (response) {
          console.log("Data Chart Berhasil Masuk:", response);
          sumbuX = response.filter((num, index) => index % 2 == 0);
          sumbuY = response.filter((num, index) => index % 2 != 0);
          // Set new default font family and font color to mimic Bootstrap's default styling
          Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
          Chart.defaults.global.defaultFontColor = '#292b2c';

          // Area Chart Example
          var ctx = document.getElementById("myAreaChart");
          var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
              labels: sumbuX,
              datasets: [{
                label: "Tagihan (Rp)",
                lineTension: 0.3,
                backgroundColor: "rgba(2,117,216,0.2)",
                borderColor: "rgba(2,117,216,1)",
                pointRadius: 5,
                pointBackgroundColor: "rgba(2,117,216,1)",
                pointBorderColor: "rgba(255,255,255,0.8)",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "rgba(2,117,216,1)",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: sumbuY,
              }],
            },
            options: {
              scales: {
                xAxes: [{
                  time: {
                    unit: 'date'
                  },
                  gridLines: {
                    display: false
                  },
                  ticks: {
                    maxTicksLimit: 7
                  }
                }],
                yAxes: [{
                  ticks: {
                    min: 0,
                    max: 1000000,
                    maxTicksLimit: 5
                  },
                  gridLines: {
                    color: "rgba(0, 0, 0, .125)",
                  }
                }],
              },
              legend: {
                display: false
              }
            }


          });
        });


    }).change();


    // SENSOR ALERT PHP (Memastikan form tidak tertutup grafik saat gagal simpan)
    if ($("#alert-meter").hasClass("alert-danger")) {
      $("#summary, #chart, #pilih_waktu, #meter_list").hide();
      $("#meter_add").show();
    } else if ($("#alert-meter").hasClass("alert-success")) {
      $("#summary, #chart, #pilih_waktu, #meter_add").hide();
      $("#meter_list").show();
    }

    $("#user_add, #user_list, #tarif_add, #tarif_list").hide();
  }
  if ($("#alert-meter").hasClass("alert-danger")) {
    $("#meter_list").hide();
    $("#meter_add").show();
  } else if ($("#alert-meter").hasClass("alert-success")) {
    $("#meter_list").show();
    $("#meter_add").hide();
  }
  // trig modal 

  // Modal Hapus User
  $(document).on("click", "button[data-bs-target='#myModal']", function () {
    var user = $(this).attr('data-user');
    $("#myModal .modal-body").text("Yakin hapus data: " + user + "?");
    $("#myModal .modal-footer form input[type='hidden']").remove();
    $("#myModal .modal-footer form").append("<input type='hidden' name='yuser' value='" + user + "'>");
  });

  // Modal Hapus Meter
  $(document).on("click", "button[data-bs-target='#modalMeter']", function () {
    var no = $(this).attr('data-no');
    $("#modalMeter .modal-body").text("Yakin hapus data: " + no + "?");
    $("#modalMeter .modal-footer form input[type='hidden']").remove();
    $("#modalMeter .modal-footer form").append("<input type='hidden' name='no' value='" + no + "'>");

  });

  // Modal Hapus Tarif
  $(document).on("click", "button[data-bs-target='#modalTarif']", function () {
    var id_tarif = $(this).attr('data-id');
    $("#modalTarif .modal-body").text("Yakin hapus data Tarif: " + id_tarif + "?");
    $("#modalTarif .modal-footer form input[type='hidden']").remove();
    $("#modalTarif .modal-footer form").append("<input type='hidden' name='id_tarif' value='" + id_tarif + "'>");
  });

});