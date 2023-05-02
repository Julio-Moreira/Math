<?php

namespace App\Entity;

use App\Repository\FunctionCalculatorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

#[ORM\Entity(repositoryClass: FunctionCalculatorRepository::class)]
class FunctionCalculator
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 32)]
        private string $law,
        private ExpressionLanguage $expressionLanguage,
    ) { }

    public function calculate(array $dom): array
    {
        $img = array_map( 
            fn($x) => $this->expressionLanguage
                ->evaluate($this->hydrateLaw($x)),
            $dom
        );
        
        return $img;
    }

    private function hydrateLaw(int $x): string {
        $output = "";

        for ($i = 0; $i < strlen($this->law); $i++) {
            $currentChar = $this->law[$i];
            $prevChar = ($i > 0) ? $this->law[$i - 1] : "";
            
            $output .= ($currentChar == "x") 
                ? ((is_numeric($prevChar)) ? '*'.$x : "$x") 
                : $currentChar;
        }

        return $output;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLaw(): string
    {
        return $this->law;
    }
}
