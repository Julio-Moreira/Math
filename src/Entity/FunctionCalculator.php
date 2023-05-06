<?php

namespace App\Entity;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FunctionCalculator
{
    private array $steps = [];

    public function __construct(
        private string $law,
        private ExpressionLanguage $expressionLanguage,
    ) { }

    public function calculate(array $domine): array
    {
        $this->addStep([
            "Init" => ['law' => $this->law, 'domine' => $domine]
        ]);

        $image = array_map( 
            fn($x) => $this->findAndResolveAllTypesOfFunction($x),
            $domine
        );
    
        return $image;
    }

    public function findAndResolveAllTypesOfFunction(int|string $x)
    {
        $tan = str_contains($this->law, "tan");
        $cos = str_contains($this->law, "cos");
        $sin = str_contains($this->law, "sin");

        if (str_contains($this->law, "log")) {
            $calculate = $this->expressionLanguage
                ->evaluate($this->calculateLogarithm($x));

            $this->addStep(['Calculate' => $calculate]);
            return $calculate;
        } elseif ($tan || $cos || $sin) {
            $calculate = $this->calculateTrigonometric($x);
            
            $this->addStep(['Calculate' => $calculate]);
            return $calculate;
        } else {
            $calculate = $this->expressionLanguage
                ->evaluate($this->calculateOthersTypes($x));

            $this->addStep(['Calculate' => $calculate]);
            return $calculate;
        }
    }

    private function calculateLogarithm(int $x): string
    {
        preg_match('/log\((.*), (.*)\)/', $this->law, $splittedLaw);

        $logarithmically = str_replace('x', $x, $splittedLaw[1]); 
        $log = LogarithmCalculator::calculate(
            $logarithmically, 
            $splittedLaw[2], 
            $this->expressionLanguage);

        $this->addStep(['Substitute' => "log($logarithmically, $splittedLaw[2])"],);
        return $log;
    }

    public function calculateTrigonometric(int|string $x): string 
    {
        preg_match('/(...)/', $this->law, $type);
        $evaluatedX = $this->expressionLanguage->evaluate($x);
        $calculateOperations = match ($type[1]) {
            'tan' => tan($evaluatedX),
            'cos' => cos($evaluatedX),
            'sin' => sin($evaluatedX),
        };

        $this->addStep(['Calculating x' => "$x : $evaluatedX"]);
        return (string) $calculateOperations;
    }

    private function calculateOthersTypes(int $x): string {
        $newLaw = "";
        for ($i = 0; $i < strlen($this->law); $i++) {
            $currentChar = $this->law[$i];
            $prevChar = ($i > 0) ? $this->law[$i - 1] : "";
            
            $newLaw .= ($currentChar == "x") 
                ? ( (is_numeric($prevChar)) ? "*$x" : "$x" ) 
                : $currentChar;
        }

        $this->addStep(['Substitute' => $newLaw]);
        return $newLaw;
    }

    private function addStep(array ...$steps): void
    {
        foreach ($steps as $step)
            array_push($this->steps, $step);
    }

    public function getLaw(): string
    {
        return $this->law;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }
}
