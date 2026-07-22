$(document).ready(function () {
  if (window.history.replaceState) { // biar kalo refresh abis add, halaman ga ngirim ulang data POST
    window.history.replaceState(null, null, window.location.href);
  }
  // Kalau jquery tidak jalan, lakukan Hard Refresh (Ctrl+F5)
  uri = window.location.href;
  params = new URLSearchParams(window.location.search);
  p = params.get("p") || "";
  console.log("URI: " + uri + " p:" + p);

  // tombol garis tiga

  $("#sidebarToggle").click(function (e) {
    e.preventDefault();
    $("body").toggleClass("sb-sidenav-toggled");
  });

  // add user

  if (p == "user" || p == "user_edit") {
    if ($("#alert-user").hasClass("alert-danger")) {
      // Jika username kembar, sembunyikan tabel dan PAKSA form tetap terbuka
      $("#summary, #chart, #pilih_waktu, #user_list").hide();
      $("#user_add").show();
    } else {
      // Jika kondisi normal (tidak ada error), jalankan alur standar bawaanmu
      $("#summary, #chart, #pilih_waktu, #user_add").hide();
      $("#user_list").show();
    }

    if (p == "user_edit") {
      $("#user_list").hide();
      $("#user_add").show();
      $("#user_form button").val("user_edit");
      $("#user_form input[name='yuser']").attr('readonly', true);
      $("<input>").attr({ type: "hidden", name: "yuser" }).val(params.get("user")).appendTo("#user_form");
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
  } else if (p == "manajemen_tarif_air" || p == "tarif_edit") {

    if ($("#alert-tarif").hasClass("alert-warning") || $("#alert-tarif").hasClass("alert-danger")) {
      $("#summary, #chart, #user_add, #user_list, #tarif_list, #pilih_waktu, #chart").hide();
      $("#tarif_add").show();
    } else {
      // Jika kondisi masuk normal tanpa eror duplikat, tampilkan tabel list biasa
      $("#summary, #chart, #user_add, #user_list, #tarif_add, #pilih_waktu, #chart").hide();
      $("#tarif_list").show();
    }

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

    if (p == "tarif_edit") {
      $("#tarif_list").hide();
      $("#tarif_add").show();
      $("#tarif_form button").val('tarif_edit');
      $("#tarif_form input[name='id_tarif']").attr('readonly', true);
      $("<input>").attr({ type: "hidden", name: "id_tarif_lama" }).val(params.get("id_tarif")).appendTo("#tarif_form");
    }

  } else if (p == "catat_edit_meter" || p == "meter_edit" || p == "pemakaian_warga") { // petugas

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

    if (p == "meter_edit") {
      $("#tarif_add, #tarif_list, #meter_list").hide();
      $("#meter_add").show();
      $("<input>").attr({ type: "hidden", name: "no" }).val(params.get("no")).appendTo("#meter_form");
      $("#meter_form button").val('meter_edit');
      $("#meter_form input[name='no']").attr('readonly', true);

    }


  }
  else if (p == "pantau_pemakaian") { // warga

    $("#summary, #chart, #pilih_waktu").hide();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list").hide();
    $("#warga_pemakaian_list").show();

    const wargaTable = document.getElementById('warga_table');
    if (wargaTable) {
      new simpleDatatables.DataTable(wargaTable);
    }
    // JARING PENGAMAN MENU BARU (Menghalangi Dashboard tembus ke Infografis & Tagihan)
  } else if (p == "tagihan_warga" || p == "tagihan_saya" || p == "infografis_warga" || p == "infografis_tagihan_warga") {
    $("#summary, #pilih_waktu").hide();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list, #warga_pemakaian_list").hide();

    // Jika menu infografis, munculkan grafiknya. Jika tagihan, sembunyikan grafik.
    if (p == "tagihan_warga" || p == "tagihan_saya") {
      $("#chart").hide();
    } else {
      $("#chart").show();
    }
  } else if (p == "settings") {
    $("#summary, #chart, #pilih_waktu").hide();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list, #warga_pemakaian_list, #activity_log_page").hide();
    $("#settings_page").show();

  } else if (p == "activity_log") {
    $("#summary, #chart, #pilih_waktu").hide();
    $("#user_add, #user_list, #tarif_add, #tarif_list, #meter_add, #meter_list, #warga_pemakaian_list, #settings_page").hide();
    $("#activity_log_page").show();

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

      // ==========================================
      // SUMMARY: JALANKAN AJAX TANPA DI-BLOCK IF KOSONG
      // ==========================================
      $.ajax({
        type: "post",
        url: "../assets/ajax.php",
        data: { p: "summary", t: bln },
        dataType: "json"
      })
        .done(function (d) {
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
          console.log("Error Summary:", xhr.responseText);
        });

      // ==========================================
      // CHART: SUNTIKKAN 't: bln' AGAR GRAFIK BISA DISARING PER BULAN
      // ==========================================
      $.ajax({
        type: "post",
        url: "../assets/ajax.php",
        data: { p: "semua_chart", t: bln }, // KUNCI UTAMA FILTER CHART
        dataType: "json"
      }).done(function (res) {
        Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';

        // Formula filter Array Ganjil-Genap 
        const getX = arr => arr.filter((_, i) => i % 2 === 0);
        const getY = arr => arr.filter((_, i) => i % 2 !== 0);
        const sumVal = arr => arr.reduce((a, b) => Number(a) + Number(b), 0);
        const formatRp = num => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);

        if (level_user === "admin" || level_user === "bendahara" || level_user === "petugas") {
          if (res.pemakaian) {
            let x = getX(res.pemakaian); let y = getY(res.pemakaian);
            $("#tot_pemakaian").text("(Total: " + sumVal(y) + " m³)");
            renderChart("chPemakaian", "line", x, y, "rgba(2,117,216,0.2)", "rgba(2,117,216,1)");
          }
          if (res.tipe_pelanggan) renderChart("chTipe", "pie", getX(res.tipe_pelanggan), getY(res.tipe_pelanggan), ["#211f9f", "#b2291f"], ["#211f9f", "#b2291f"]);
          if (res.tercatat) renderChart("chTercatat", "bar", getX(res.tercatat), getY(res.tercatat), "rgba(40,167,69,1)", "rgba(40,167,69,1)");
          if (res.belum_tercatat) renderChart("chBlmTercatat", "bar", getX(res.belum_tercatat), getY(res.belum_tercatat), "rgba(220,53,69,1)", "rgba(220,53,69,1)");
        }

        if (level_user === "admin" || level_user === "bendahara") {
          if (res.tagihan) {
            let x = getX(res.tagihan); let y = getY(res.tagihan);
            $("#tot_tagihan").text("(Total: " + formatRp(sumVal(y)) + ")");
            renderChart("chTagihan", "line", x, y, "rgba(255,193,7,0.2)", "rgba(255,193,7,1)");
          }
          if (res.pemasukan) renderChart("chPemasukan", "line", getX(res.pemasukan), getY(res.pemasukan), "rgba(40,167,69,0.2)", "rgba(40,167,69,1)");
          if (res.sdh_lunas) renderChart("chLunas", "bar", getX(res.sdh_lunas), getY(res.sdh_lunas), "rgba(2,117,216,1)", "rgba(2,117,216,1)");
          if (res.blm_lunas) renderChart("chBlmLunas", "bar", getX(res.blm_lunas), getY(res.blm_lunas), "rgba(220,53,69,1)", "rgba(220,53,69,1)");
        }

        if (level_user === "warga") {
          if (res.pemakaian_warga) {
            let x = getX(res.pemakaian_warga); let y = getY(res.pemakaian_warga);
            $("#tot_pemakaian_w").text("(Total: " + sumVal(y) + " m³)");
            renderChart("chPemakaianWarga", "bar", x, y, "rgba(2,117,216,1)", "rgba(2,117,216,1)");
          }
          if (res.tagihan_warga) {
            let x = getX(res.tagihan_warga); let y = getY(res.tagihan_warga);
            $("#tot_tagihan_w").text("(Total: " + formatRp(sumVal(y)) + ")");
            renderChart("chTagihanWarga", "line", x, y, "rgba(40,167,69,0.2)", "rgba(40,167,69,1)");
          }
        }
      });

    }).change();
    // Letakkan tepat di bawah blok "LOGIKA AJAX DASHBOARD ROLE-BASED"
    var chartInst = {}; // Brankas instansi Chart global
    function renderChart(id, type, x, y, bg, border) {
      if (!document.getElementById(id)) return;
      var ctx = document.getElementById(id).getContext('2d');
      if (chartInst[id]) chartInst[id].destroy(); // Hancurkan chart usang jika di-refresh
      chartInst[id] = new Chart(ctx, {
        type: type,
        data: { labels: x, datasets: [{ label: "Jumlah", backgroundColor: bg, borderColor: border, fill: true, data: y }] },
        options: { legend: { display: false } }
      });
    }


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
    $("#myModal .modal-footer form").append($("<input>").attr({ type: "hidden", name: "yuser" }).val(user));
  });

  // Modal Hapus Meter
  $(document).on("click", "button[data-bs-target='#modalMeter']", function () {
    var no = $(this).attr('data-no');
    $("#modalMeter .modal-body").text("Yakin hapus data: " + no + "?");
    $("#modalMeter .modal-footer form input[type='hidden']").remove();
    $("#modalMeter .modal-footer form").append($("<input>").attr({ type: "hidden", name: "no" }).val(no));

  });

  // Modal Hapus Tarif
  $(document).on("click", "button[data-bs-target='#modalTarif']", function () {
    var id_tarif = $(this).attr('data-id');
    $("#modalTarif .modal-body").text("Yakin hapus data Tarif: " + id_tarif + "?");
    $("#modalTarif .modal-footer form input[type='hidden']").remove();
    $("#modalTarif .modal-footer form").append($("<input>").attr({ type: "hidden", name: "id_tarif" }).val(id_tarif));
  });

});