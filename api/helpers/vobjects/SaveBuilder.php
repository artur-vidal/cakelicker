<?php

    namespace Cakelicker\ValueObjects;
    use Cakelicker\Traits\ValidationTraits;
    use Cakelicker\ValueObjects\Save;
    
    class SaveBuilder
    {
        use ValidationTraits;

        private $name = null;
        private $cakesCoefficient = null;
        private $cakesExponent = null;
        private $xp = null;
        private $level = null;
        private $prestige = null;
        private $rebirths = null;

        public function fillFromAssocArrayAndBuild($array) {
            $this->fillFromAssocArray($array);
            return $this->build();
        }

        public function fillFromAssocArray($array) {
            $this->withName($array['name'] ?? null)
                ->withCakesCoefficient($array['cakes_coefficient'] ?? null)
                ->withCakesExponent($array['cakes_exponent'] ?? null)
                ->withXp($array['xp'] ?? null)
                ->withLevel($array['level'] ?? null)
                ->withPrestige($array['prestige'] ?? null)
                ->withRebirths($array['rebirths'] ?? null);
        }

        public function build() {
            if ($this->countSetFields() === 0) {
                throw new \Exception('Nenhuma informação fornecida para criar ou atualizar save.', 400);
            }

            return new Save(
                $this->name,
                $this->cakesCoefficient,
                $this->cakesExponent,
                $this->xp,
                $this->level,
                $this->prestige,
                $this->rebirths
            );
        }

        public function withName($name) {
            $this->name = $name;
            return $this;
        }

        public function withCakesCoefficient($cakesCoefficient) {
            $this->cakesCoefficient = $cakesCoefficient;
            return $this;
        }

        public function withCakesExponent($cakesExponent) {
            $this->cakesExponent = $cakesExponent;
            return $this;
        }

        public function withXp($xp) {
            $this->xp = $xp;
            return $this;
        }

        public function withLevel($level) {
            $this->level = $level;
            return $this;
        }

        public function withPrestige($prestige) {
            $this->prestige = $prestige;
            return $this;
        }

        public function withRebirths($rebirths) {
            $this->rebirths = $rebirths;
            return $this;
        }

        public function isComplete() {
            return $this->countSetFields() === 7;
        }

        public function isEmpty() {
            return $this->countSetFields() === 0;
        }

        private function countSetFields() {
            $fields = [
                'name' => $this->name,
                'cakes_coefficient' => $this->cakesCoefficient,
                'cakes_exponent' => $this->cakesExponent,
                'xp' => $this->xp,
                'level' => $this->level,
                'prestige' => $this->prestige,
                'rebirths' => $this->rebirths
            ];

            $set_fields = array_filter($fields, fn($value) => $value !== null);
            return count($set_fields);
        }
    }

?>