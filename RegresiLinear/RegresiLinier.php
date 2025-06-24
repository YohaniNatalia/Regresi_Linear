<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regresi Linear</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #555;
        }
        input, button, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            height: 100px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #45a049;
        }
        .reset-button {
            background-color: #f44336;
        }
        .reset-button:hover {
            background-color: #e53935;
        }
        .result {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 20px;
        }
        .result h2 {
            margin-top: 0;
            color: #333;
        }
        .result ul {
            padding-left: 20px;
        }
        .result ul li {
            margin-bottom: 10px;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Regresi Linear</h1>
        <form method="POST">
            <label for="x">Masukkan nilai X (pisahkan dengan koma):</label>
            <textarea id="x" name="x" placeholder="Contoh: 1, 2, 3, 4" required></textarea>

            <label for="y">Masukkan nilai Y (pisahkan dengan koma):</label>
            <textarea id="y" name="y" placeholder="Contoh: 2, 4, 6, 8" required></textarea>

            <label for="x_forecast">Masukkan nilai X untuk prediksi (opsional, pisahkan dengan koma):</label>
            <textarea id="x_forecast" name="x_forecast" placeholder="Contoh: 5, 6, 7"></textarea>

            <button type="submit">Hitung Regresi</button>
            <button type="button" class="reset-button" onclick="resetForm()">Reset</button>

            <script>
                function resetForm() {
                    document.querySelector("form").reset();
                    const resultDiv = document.querySelector(".result");
                    if (resultDiv) {
                        resultDiv.innerHTML = ""; // Hapus hasil perhitungan jika ada
                    }
                }
            </script>

        <?php
        class RegresiLinier {
            public $x, $y, $n, $x2, $y2, $xy, $a, $b, $all;

            public function __construct($x = null, $y = null) {
                if (!is_null($x) && !is_null($y)) {
                    $this->x = $x;
                    $this->y = $y;
                    $this->compute();
                }
            }

            public function compute() {
                if (is_array($this->x) && is_array($this->y)) {
                    if (count($this->x) == count($this->y)) {
                        $this->n = count($this->x);
                        $this->prepare_calculation();
                        $this->ab();
                        $this->linear_regression();
                    } else {
                        throw new Exception('Jumlah data variabel X dan Y harus sama');
                    }
                } else {
                    throw new Exception('Variabel X atau Y belum didefinisikan');
                }
            }

            public function prepare_calculation() {
                $this->x2 = array_map(fn($n) => $n * $n, $this->x);
                $this->y2 = array_map(fn($n) => $n * $n, $this->y);

                for ($i = 0; $i < $this->n; $i++) {
                    $this->xy[$i] = $this->x[$i] * $this->y[$i];
                }
            }

            public function ab() {
                $sum_x = array_sum($this->x);
                $sum_y = array_sum($this->y);
                $sum_x2 = array_sum($this->x2);
                $sum_xy = array_sum($this->xy);
            
                $denominator = ($this->n * $sum_x2) - ($sum_x ** 2);
                if ($denominator == 0) {
                    throw new Exception('Kesalahan: Penyebut dalam perhitungan A dan B adalah nol.');
                }
            
                $this->a = (($sum_y * $sum_x2) - ($sum_x * $sum_xy)) / $denominator;
                $this->b = (($this->n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;
            }
            

            public function forecast($xfore) {
                return $this->a + ($this->b * $xfore);
            }

            public function linear_regression() {
                foreach ($this->x as $index => $x_value) {
                    $this->all[$index] = $this->forecast($x_value);
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $x = array_map('floatval', explode(',', $_POST['x']));
                $y = array_map('floatval', explode(',', $_POST['y']));
                $x_forecast = isset($_POST['x_forecast']) ? array_map('floatval', explode(',', $_POST['x_forecast'])) : [];

                $regresi = new RegresiLinier($x, $y);

                echo "<div class='result'>";
                echo "<h2>Hasil Perhitungan</h2>";
                echo "<p><strong>Persamaan Regresi:</strong> Y = " . number_format($regresi->a, 4) . " + " . number_format($regresi->b, 4) . "X</p>";
                echo "<p><strong>Nilai A:</strong> " . number_format($regresi->a, 4) . "</p>";
                echo "<p><strong>Nilai B:</strong> " . number_format($regresi->b, 4) . "</p>";

                echo "<h3>Prediksi Nilai Y Berdasarkan X</h3>";
                echo "<ul>";
                foreach ($x as $index => $xi) {
                    $prediksi = $regresi->forecast($xi);
                    echo "<li>X: $xi, Y: " . number_format($prediksi, 4) . "</li>";
                }
                echo "</ul>";

                if (!empty($x_forecast)) {
                    echo "<h3>Prediksi Nilai Y untuk Input Baru</h3>";
                    echo "<ul>";
                    foreach ($x_forecast as $xf) {
                        $forecast = $regresi->forecast($xf);
                        echo "<li>X: $xf, Y: " . number_format($forecast, 4) . "</li>";
                    }
                    echo "</ul>";
                }

                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='result error'><strong>Error:</strong> " . $e->getMessage() . "</div>";
            }
        }
        ?>
    </div>
</body>
</html>
