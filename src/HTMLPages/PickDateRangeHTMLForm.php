<?php


class PickDateRangeHTMLForm
{
    public $formURL;
    public $fechaInicio;
    public $fechaFin;

    public function __construct($formURL, $fechaInicio = "", $fechaFin = "")
    {
        $this->formURL    = $formURL;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin    = $fechaFin;
    }

    public function render()
    {
        ob_start(); ?>

        <h3>Seleccionar rangos de fechas</h3>
            
        <form 
        action="<?php echo $this->formURL; ?>" 
        method="get" 
        onsubmit="return validateDates();">
                <label for="fechaInicio">Fecha Inicio:</label>
                <input type="datetime-local" name="fechaInicio" id="fechaInicio" value="<?php echo $this->fechaInicio; ?>">

                <label for="fechaFin">Fecha Fin:</label>
                <input type="datetime-local" name="fechaFin" id="fechaFin" value="<?php echo $this->fechaFin; ?>">
            
                <input type="submit" value="Submit">
            </form>
            
        <?php

        $now = new DateTime();
            
        $today = new DateTime();
        $today->setTime(0,0);
            
        $dayOfWeek = $today->format('N');
            
        // Calculate the start and end of "last week"
        $lastMonday = new DateTime();
        $lastMonday->modify('-' . ($dayOfWeek + 6) % 7 . ' days');
        $lastSunday = clone $lastMonday;
        $lastSunday->modify('+6 days');
            
        // Calculate "current month" start
        $currentMonthStart = new DateTime($today->format('Y-m-01\TH:i'));
            
        // Calculate "current week" start (Monday)
        $currentWeekStart = new DateTime();
        $currentWeekStart->modify('-' . ($today->format('N') - 1) . ' days');
            
        // Calculate "last year" start and end
        $lastYearStart = new DateTime($today->format('Y-01-01\TH:i'));
        $lastYearStart->modify('-1 year');
        $lastYearEnd = new DateTime($today->format('Y-12-31\TH:i'));
        $lastYearEnd->modify('-1 year');
            
        // Calculate "last month" start and end
        $lastMonthStart = new DateTime($today->format('Y-m-01\TH:i'));
        $lastMonthStart->modify('-1 month');
            
        $lastMonthEnd = new DateTime($today->format('Y-m-t\TH:i'));
        $lastMonthEnd->modify('-1 month');
            
        // Current date and time
        $today = new DateTime();
            
        // "Current month" range
        $currentMonthStart = (clone $today)->modify('first day of this month')->setTime(0, 0);
            
        // "Current week" range (from Monday to the current date and time)
        $currentWeekStart = (clone $today)->modify('-' . ($today->format('N') - 1) . ' days')->setTime(0, 0);
            
        // "Last week" range (from Monday to Sunday)
        $lastWeekStart = (clone $today)->modify('-7 days')->modify('-' . ($today->format('N') - 1) . ' days')->setTime(0, 0);
        $lastWeekEnd = (clone $lastWeekStart)->modify('+6 days')->setTime(23, 59, 59);
            
        // "Last month" range
        $lastMonthStart = (clone $today)->modify('first day of last month')->setTime(0, 0);
        $lastMonthEnd = (clone $today)->modify('last day of last month')->setTime(23, 59, 59);
            
        // "Last year" range
        $lastYearStart = (clone $today)->modify('first day of January last year')->setTime(0, 0);
        $lastYearEnd = (clone $today)->modify('last day of December last year')->setTime(23, 59, 59);
            
        $yesterday = (clone $today)->modify('-1 day');
            
        $currentYearStart = (clone $today)->modify('first day of January this year')->setTime(0, 0);
            
        // Links for predefined date ranges
        $links = [
            'Hoy' => [
                'startDate' => $today->setTime(0,0)->format('Y-m-d\TH:i'),
                'endDate'   => $now->format('Y-m-d\TH:i'),
            ],
            'Ayer' => [
                'startDate' => $yesterday->setTime(0,0)->format('Y-m-d\TH:i'),
                'endDate'   => $yesterday->setTime(23,59,59)->format('Y-m-d\TH:i'),
            ],
            'Mes actual' => [
                'startDate' => $currentMonthStart->format('Y-m-d\TH:i'),
                'endDate'   => $now->format('Y-m-d\TH:i')
            ],
            'Semana actual' => [
                'startDate' => $currentWeekStart->format('Y-m-d\TH:i'),
                'endDate'   => $now->format('Y-m-d\TH:i')
            ],
            'Última semana' => [
                'startDate' => $lastWeekStart->format('Y-m-d\TH:i'),
                'endDate'   => $lastWeekEnd->format('Y-m-d\TH:i')
            ],
            // TO MUCH MEMORY
            'Mes pasado' => [
                'startDate' => $lastMonthStart->format('Y-m-d\TH:i'),
                'endDate' => $lastMonthEnd->format('Y-m-d\TH:i')
            ],
            'Año actual' => [
                'startDate' => $currentYearStart->format('Y-m-d\TH:i'),
                'endDate'   => $now->format('Y-m-d\TH:i'),
            ],
            'Año pasado' => [
                'startDate' => $lastYearStart->format('Y-m-d\TH:i'),
                'endDate'   => $lastYearEnd->format('Y-m-d\TH:i')
            ],
        ];

        foreach ($links as $label => $dates) {
            echo "<a href=\"" . $this->formURL . "?fechaInicio=" . $dates['startDate'] . "&fechaFin=" . $dates['endDate'] . "\"><button type=\"button\">$label</button></a> ";
        }

        return ob_get_clean();
    }
}
