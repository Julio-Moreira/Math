<?php

namespace App\Entity;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FunctionCalculator
{
    private array $stepByStep;

    public function __construct(
        private string $law,
        private ExpressionLanguage $expressionLanguage,
    ) { 
        $this->stepByStep = [];
    }

    public function calculate(array $dom): array
    {
        $this->addStep(["Init" => $this->law]);
        $img = array_map( 
            fn($x) => $this->findAndResolveAllTypesOfFunction($x),
            $dom
        );
    
        return $img;
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
        preg_match('/log\((.*), (.*)\)(.*)/', $this->law, $splittedLaw);

        $logarithmically = str_replace('x', $x, $splittedLaw[1]); 
        $log = log(
            $this->expressionLanguage->evaluate($logarithmically), 
            $this->expressionLanguage->evaluate($splittedLaw[2])
        );

        $this->addStep(['Substitute' => "log($logarithmically, $splittedLaw[2])"]);
        $this->addStep(['Calculating Logarithm' => "$log"]);
        
        $finalString = "$log $splittedLaw[3]";
        $this->addStep(["Join" => $finalString]);
        return $finalString;
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
        $output = "";

        for ($i = 0; $i < strlen($this->law); $i++) {
            $currentChar = $this->law[$i];
            $prevChar = ($i > 0) ? $this->law[$i - 1] : "";
            
            $output .= ($currentChar == "x") 
                ? ((is_numeric($prevChar)) ? '*'.$x : "$x") 
                : $currentChar;
        }

        $this->addStep(['Substitute' => $output]);
        return $output;
    }

    private function addStep(array $step): void
    {
        array_push($this->stepByStep, $step);
    }

    public function getLaw(): string
    {
        return $this->law;
    }

    public function getSteps(): array
    {
        return $this->stepByStep;
    }
}
