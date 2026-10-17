<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../helpers/response.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // get disini
        // GET ny ID 
        // url: mahasiswa.php?id=[id]
        if (isset($_GET['id'])) {
            //dimulai dari sini
            $id = $_GET['id'];

            $q = "SELECT m.id,
                        m.nama, 
                        m.nim,
                        j.nama_jurusan AS jurusan 
                        FROM mahasiswa m 
                        LEFT JOIN jurusan j ON m.jurusan_id = j.id
                        WHERE m.id = '$id'";

            $r = mysqli_query($koneksi, $q);

            if (!$r) {
                sendResponse(
                    false,
                    "query gagal: " . mysqli_error($koneksi),
                    null,
                    500
                );
            }

            $data = mysqli_fetch_assoc($r);

            if (!$data) {
                sendResponse(
                    false,
                    "mahasiswa tidak ditemukan",
                    null,
                    404
                );
            }


            sendResponse(true, "berhasil", $data, 200);
        }

        //search
        if (isset($_GET['search'])) {
            $search = $_GET['search'];

            $q = "SELECT m.id,
                        m.nama, 
                        m.nim,
                        j.nama_jurusan AS jurusan 
                        FROM mahasiswa m 
                        LEFT JOIN jurusan j
                        ON m.jurusan_id = j.id
                        WHERE m.nama LIKE '%$search%' 
                        OR m.nim LIKE '%$search%'
                        ORDER BY m.id DESC";

            $r = mysqli_query($koneksi, $q);

            if (!$r) {
                sendResponse(
                    false,
                    "query gagal: " . mysqli_error($koneksi),
                    null,
                    500
                );
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        //pagination
        if (isset($_GET['page']) || isset($_GET['limit'])) {

            $page = isset($_GET['page'])
                ? (int)$_GET['page']
                : 1;

            $limit = isset($_GET['limit'])
                ? (int)$_GET['limit']
                : 10;

            if ($page < 1) {
                $page = 1;
            }

            if ($limit < 1) {
                $limit = 10;
            }

            $offset = ($page - 1) * $limit;


            $q = "SELECT m.id,
                        m.nama, 
                        m.nim,
                        j.nama_jurusan AS jurusan 
                        FROM mahasiswa m 
                        LEFT JOIN jurusan j
                        ON m.jurusan_id = j.id
                        ORDER BY m.id DESC
                        LIMIT $limit OFFSET $offset";
            // ini integer, tidak perlu ''

            $r = mysqli_query($koneksi, $q);

            if (!$r) {
                sendResponse(
                    false,
                    "query gagal: " . mysqli_error($koneksi),
                    null,
                    500
                );
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
         break;
    case 'POST':
        // post disini
        $input = json_decode(file_get_contents('php://input'), true);
        //cek apakah  nim, nama dan jurusan_id ada?
        if(  $input || !isset($input['nama']) || !isset($input['nim'])  || !isset($input['jurusan_id']) ){
            sendResponse(false, "field nama, nim, jurusan_id tidak boleh kosong", null, 400);
        }

        $nama = $input['nama'];
        $nim = $input['nim'];
        $jurusan_id = $input['jurusan_id'];


        // cek apakah jurusan ada?
        $cek_jurusan = mysqli_query($koneksi, "SELECT * FROM jurusan WHERE id = '$jurusan_id'");
        if (mysqli_num_rows($cek_jurusan) == 0) {
            sendResponse(false, "jurusan tidak ditemukan", null, 404);
        }

        //cek apakah nim sudah ada atau belum di tabel mahasiswa
        $cek_nim = mysqli_query($koneksi, "SELECT id FROM mahasiswa where nim='$nim' ");
        if(mysqli_num_rows($cek_nim) > 0) {
            sendResponse(false, "nim sudah terdaftar", null, 409);
        }

        // insert data
        $q = "INSERT INTO mahasiswa (nama,nim,jurusa_id,created_at) VALUE ('$nama', '$nim', '$jurusan_id', CURDATE())";

        if(mysqli_query($koneksi, $q)) {
            sendResponse(true, "data tersimpan", ["id" => mysqli_insert_id($koneksi)], 201);
        } else {
            sendResponse(false, mysql_error($koneski), null, 500);
        }

        break;
    default:
        sendResponse(false, "method no allownd", null, 405);
        break;
    }

    die;
        

