<?php
//verificar al menos 3 datos en el front porque si no se rompe
$demanda =$_POST["demanda"];
$todo = array();
function meanSimpleMovingAverage($x) {
    if (count($x) < 3) {
        exit("meanSimpleMovingAverage requires at least three data points");
    }

    $movilsimple = [];

    for ($i = 2; $i < count($x); $i++) {
        $movil = round(($x[$i - 2] + $x[$i - 1] + $x[$i]) / 3, 2);
        $movilsimple[] = $movil;
    }

    return $movilsimple;
}

function linearRegression() {
    $m = 0;
    $b = 0;
    $data = [[1, 256],
    [2, 312],
    [3, 426],
    [4, 278],
    [5, 298],
    [6, 387],
    [7, 517],
    [8, 349]];
    // Store data length in a local variable to reduce
    // repeated array property lookups
    $dataLength = count($data);

    // If there's only one point, arbitrarily choose a slope of 0
    // and a y-intercept of whatever the y of the initial point is
    if ($dataLength === 1) {
        $m = 0;
        $b = $data[0][1];
    } else {
        // Initialize our sums and scope the `m` and `b`
        // variables that define the line.
        $sumX = 0;
        $sumY = 0;
        $sumXX = 0;
        $sumXY = 0;

        // Use local variables to grab point values
        // with minimal array property lookups
        $point = [];
        $x = 0;
        $y = 0;

        // Gather the sum of all x values, the sum of all
        // y values, and the sum of x^2 and (x*y) for each
        // value.
        //
        // In math notation, these would be SS_x, SS_y, SS_xx, and SS_xy
        for ($i = 0; $i < $dataLength; $i++) {
            $point = $data[$i];
            $x = $point[0];
            $y = $point[1];

            $sumX += $x;
            $sumY += $y;

            $sumXX += $x * $x;
            $sumXY += $x * $y;
        }

        // `m` is the slope of the regression line
        $m =
            ($dataLength * $sumXY - $sumX * $sumY) /
            ($dataLength * $sumXX - $sumX * $sumX);

        // `b` is the y-intercept of the line.
        $b = $sumY / $dataLength - ($m * $sumX) / $dataLength;
    }

    // Return both values as an array.
    return [
        'm' => $m,
        'b' => $b
    ];
}


class SimpleExponentialSmoothing
{
    private $data;
    private $alpha;
    private $forecast;

    public function __construct($data, $alpha)
    {
        if ($data == null) {
            throw new Exception("data parameter is null");
        } elseif (count($data) < 2) {
            throw new Exception("data doesn't contain enough data to make a prediction");
        }

        if ($alpha > 1 || $alpha < 0) {
            throw new Exception("alpha parameter must be between 0 and 1");
        }

        $this->data = $data;
        $this->alpha = $alpha;
        $this->forecast = null;
    }

    public function predict()
    {
        $forecast = array();
        $forecast[0] = null;
        $forecast[1] = 0.5 * ($this->data[0] + $this->data[1]);

        for ($i = 2; $i <= count($this->data); ++$i) {
            $forecast[$i] = $this->alpha * ($this->data[$i - 1] - $forecast[$i - 1]) + $forecast[$i - 1];
        }

        $this->forecast = $forecast;
        return $forecast;
    }

    public function getForecast()
    {
        if ($this->forecast == null) {
            $this->predict();
        }
        return $this->forecast;
    }

    public function computeMeanSquaredError()
    {
        $SSE = 0.0;
        $n = 0;
        for ($i = 0; $i < count($this->data); ++$i) {
            if ($this->data[$i] != null && $this->forecast[$i] != null) {
                $SSE += pow($this->data[$i] - $this->forecast[$i], 2);
                $n++;
            }
        }
        return 1 / ($n - 1) * $SSE;
    }

    public function optimizeParameter($iter)
    {
        $incr = 1 / $iter;
        $bestAlpha = 0.0;
        $bestError = -1;
        $this->alpha = $bestAlpha;

        while ($this->alpha < 1) {
            $forecast = $this->predict();
            $error = $this->computeMeanSquaredError();
            if ($error < $bestError || $bestError == -1) {
                $bestAlpha = $this->alpha;
                $bestError = $error;
            }
            $this->alpha += $incr;
        }

        $this->alpha = $bestAlpha;
        return $this->alpha;
    }
}

function testPredict($x,$y)
    {
        $ses = new SimpleExponentialSmoothing($x, $y);
        $result = $ses->predict();
        return $result;
     }




$rl = linearRegression(); 
$pms = testPredict($demanda, 0.2); 
$ses = meanSimpleMovingAverage($demanda);     
$todo = [
         'demanda' => $demanda,
         'movingAverage' => $pms,
         'exponentialSmoothing' => $ses,
         'regressionLine' => $rl
     ];

echo json_encode($todo, JSON_UNESCAPED_UNICODE);
?>


