<?php

namespace Cakelicker\InputObjects;

class SaveInput extends InputObject {

    private $name;
    private $cakesCoefficient;
    private $cakesExponent;
    private $xp;
    private $level;
    private $prestige;
    private $rebirths;

    public function fillFromAssocArray($array) {
        $this->withName($array['name'] ?? null)
            ->withCakesCoefficient($array['cakesCoefficient'] ?? null)
            ->withCakesExponent($array['cakesExponent'] ?? null)
            ->withXp($array['xp'] ?? null)
            ->withLevel($array['level'] ?? null)
            ->withPrestige($array['prestige'] ?? null)
            ->withRebirths($array['rebirths'] ?? null);
    }

    public function withName($name) {
        $this->setIfPresent($name, 'name');
        return $this;
    }

    public function withCakesCoefficient($cakesCoefficient) {
        $this->setIfPresent($cakesCoefficient, 'cakesCoefficient');
        return $this;
    }

    public function withCakesExponent($cakesExponent) {
        $this->setIfPresent($cakesExponent, 'cakesExponent');
        return $this;
    }

    public function withXp($xp) {
        $this->setIfPresent($xp, 'xp');
        return $this;
    }

    public function withLevel($level) {
        $this->setIfPresent($level, 'level');
        return $this;
    }

    public function withPrestige($prestige) {
        $this->setIfPresent($prestige, 'prestige');
        return $this;
    }

    public function withRebirths($rebirths) {
        $this->setIfPresent($rebirths, 'rebirths');
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getCakesCoefficient() {
        return $this->cakesCoefficient;
    }

    public function getCakesExponent() {
        return $this->cakesExponent;
    }

    public function getXp() {
        return $this->xp;
    }

    public function getLevel() {
        return $this->level;
    }

    public function getPrestige() {
        return $this->prestige;
    }

    public function getRebirths() {
        return $this->rebirths;
    }

    public function toAssocArray() {
        return [
            'name' => $this->getName(),
            'cakes_coefficient' => $this->getCakesCoefficient(),
            'cakes_exponent' => $this->getCakesExponent(),
            'xp' => $this->getXp(),
            'level' => $this->getLevel(),
            'prestige' => $this->getPrestige(),
            'rebirths' => $this->getRebirths()
        ];
    }

    public function isComplete() {
        return $this->countSetFields() === 7;
    }

    public function isEmpty() {
        return $this->countSetFields() === 0;
    }
}
?>
