<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Nilai</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
        }

        .container h3 {
            margin-bottom: 24px;
            color: #2c3e50;
            text-align: center;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 0.95rem;
            color: #2d3748;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15);
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #3182ce;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: #2b6cb0;
        }
    </style>
</head>
<body>

    <div class="container">
        <h3>Form Nilai</h3>
        <form action="data_nilai.php" method="post">
            <div class="form-group">
                <label for="nim">NIM</label>
                <input type="text" id="nim" name="nim" placeholder="Masukkan NIM" required>
            </div>

            <div class="form-group">
                <label for="nm_lengkap">Nama Lengkap</label>
                <input type="text" id="nm_lengkap" name="nm_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="file_path">URL Foto</label>
                <input type="url" id="file_path" name="file_path" placeholder="https://example.com/foto.jpg">
            </div>

            <div class="form-group">
                <label for="nilai_akhir">Nilai Akhir</label>
                <input type="number" id="nilai_akhir" name="nilai_akhir" step="0.01" placeholder="Contoh: 85" required>
            </div>

            <div class="form-group">
                <label for="nilai_huruf">Nilai Huruf</label>
                <input type="text" id="nilai_huruf" name="nilai_huruf" placeholder="Contoh: A">
            </div>

            <div class="form-group">
                <label for="predikat">Predikat</label>
                <input type="text" id="predikat" name="predikat" placeholder="Contoh: Sangat Memuaskan">
            </div>

            <button type="submit" class="btn-submit">Submit</button>
        </form>
    </div>

</body>
</html>