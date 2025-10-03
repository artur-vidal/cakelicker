<?php

    namespace Cakelicker\InputObjects;
    use Cakelicker\Traits\ValidationTraits;
    
    abstract class InputObject {

        use ValidationTraits;

        protected function setIfPresentAndValid($value, $propertyName, $validationMethod, $exceptionMessage) {
            if($value !== null) {
                if(!$this->$validationMethod($value))
                    throw new \Exception($exceptionMessage, 400);

                $this->setIfPresent($value, $propertyName);
            }
        }

        protected function setIfPresent($value, $propertyName) {
            $this->$propertyName = $value;
        }

        abstract public function fillFromAssocArray($array);
        
        public function toAssocArrayWithoutNulls() {
            return array_filter($this->toAssocArray(), fn($value) => $value !== null);
        }
        abstract public function toAssocArray();

        abstract protected function isComplete();
        abstract protected function isEmpty();
        
        protected function countSetFields() {
            return count($this->toAssocArrayWithoutNulls());
        }

    }

?>