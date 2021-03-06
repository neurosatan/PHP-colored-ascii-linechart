<?php
declare(strict_types = 1);

namespace noximo\PHPColoredAsciiLinechart;

/**
 * Class Graph
 * @package noximo\PHPColoredConsoleLinegraph
 */
class Chart
{
    /**
     * @var array
     */
    private $results = [];

    /**
     * @var Settings
     */
    private $settings;
    /**
     * @var float
     */
    private $min;
    /**
     * @var float
     */
    private $max;
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $allTimeMaxHeight = 0;
    private $chart;

    public function __construct(Linechart $chart)
    {
        $this->chart = $chart;
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @param Settings $settings
     *
     * @return Chart
     */
    public function setSettings(Settings $settings): Chart
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return float
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * @param float $min
     *
     * @return Chart
     */
    public function setMin(float $min): Chart
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return float
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * @param float $max
     *
     * @return Chart
     */
    public function setMax(float $max): Chart
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return Chart
     */
    public function setWidth(int $width): Chart
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @param array $result
     */
    public function addResult(array $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return Chart
     */
    public function printAndwait(): Chart
    {
        $this->print()->wait();

        return $this;
    }

    /**
     * @return Chart
     */
    public function wait(): Chart
    {
        usleep((int) round(1000000 / $this->settings->getFps()));

        return $this;
    }

    /**
     * @return Chart
     */
    public function print(): Chart
    {
        $fopen = fopen('php://stdout', 'wb');
        if (\is_resource($fopen)) {
            fwrite($fopen, $this->__toString());
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $return = '';
        foreach ($this->merge() as $row) {
            foreach ($row as $cell) {
                $return .= $cell;
            }
            $return .= $this->settings->getColorizer()->getEOL();
        }

        return $this->settings->getColorizer()->processFinalText($return);
    }

    /**
     * @return array
     */
    private function merge(): array
    {
        $x = 0;
        $merged = [];
        foreach ($this->results as $result) {
            foreach ($result as $x => $row) {
                foreach ($row as $y => $cell) {
                    if ($this->shouldBeMerged($merged, (string)$x, (string)$y, (string)$cell)) {
                        $merged[$x][$y] = (string) $cell;
                    }
                }
            }
        }

        if ($x < $this->allTimeMaxHeight) {
            $width = $this->width + \strlen($this->settings->getPadding());
            for ($i = 0, $iMax = $this->allTimeMaxHeight - $x; $i < $iMax; $i++) {
                $merged[] = array_fill(0, $width, ' ');
            }
        }

        return $merged;
    }

    /**
     * @param array $merged
     * @param string $x
     * @param string $y
     * @param string $cell
     *
     * @return bool
     */
    private function shouldBeMerged(array $merged, string $x, string $y, string $cell): bool
    {
        return !isset($merged[$x][$y]) || ($cell !== ' ' && strpos($merged[$x][$y], 'm') === false);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $return = [];
        foreach ($this->merge() as $row) {
            foreach ($row as $cell) {
                $return [] = $cell;
            }
        }

        return $return;
    }

    /**
     * @param bool $useAlternativeMethod
     *
     * @return Chart
     */
    public function clearScreen(bool $useAlternativeMethod = null): Chart
    {
        $output = fopen('php://stdout', 'wb');
        if (\is_resource($output)) {
            if ($useAlternativeMethod) {
                fwrite($output, \chr(27) . \chr(91) . 'H' . \chr(27) . \chr(91) . 'J');   //^[H^[J
            } else {
                fwrite($output, "\033[0;0f"); //MoveCursor
            }
        }

        return $this;
    }

    /**
     * @return float
     */
    public
    function getAllTimeMaxHeight(): float
    {
        return $this->allTimeMaxHeight;
    }

    /**
     * @param int $allTimeMaxHeight
     *
     * @return Chart
     */
    public
    function setAlltimeMaxHeight(int $allTimeMaxHeight): Chart
    {
        $this->allTimeMaxHeight = $allTimeMaxHeight;

        return $this;
    }
}
