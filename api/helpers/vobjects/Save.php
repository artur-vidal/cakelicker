<?php

namespace Cakelicker\ValueObjects;
use Cakelicker\Traits\ValidationTraits;

class Save {

    private $name;
    private $cakesCoefficient;
    private $cakesExponent;
    private $xp;
    private $level;
    private $prestige;
    private $rebirths;
    private $savepath;

    public function __construct(
        $name = null,
        $cakesCoefficient = null,
        $cakesExponent = null,
        $xp = null,
        $level = null,
        $prestige = null,
        $rebirths = null
    ) {
        $this->setIfPresent($name,             'name');
        $this->setIfPresent($cakesCoefficient, 'cakesCoefficient');
        $this->setIfPresent($cakesExponent,    'cakesExponent');
        $this->setIfPresent($xp,               'xp');
        $this->setIfPresent($level,            'level');
        $this->setIfPresent($prestige,         'prestige');
        $this->setIfPresent($rebirths,         'rebirths');
    }

    private function setIfPresent($value, $propertyName) {
        $this->$propertyName = $value;
    }

    public function toAssocArray() {
        return array_filter([
            'name'             => $this->getName(),
            'cakes_coefficient' => $this->getCakesCoefficient(),
            'cakes_exponent'    => $this->getCakesExponent(),
            'xp'               => $this->getXp(),
            'level'            => $this->getLevel(),
            'prestige'         => $this->getPrestige(),
            'rebirths'         => $this->getRebirths()
        ], fn($value) => $value !== null);
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
}

?>
