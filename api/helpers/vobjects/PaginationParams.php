<?php

    namespace Cakelicker\ValueObjects;

    class PaginationParams {
        private $page;
        private $offset;
        private $perPageLimit;
        private $sortByColumn;
        private $sortDirection;

        private $firstIndex;
        private $sortingColumnsWhitelist;
        private $sortingDirectionsWhitelist;

        public function __construct($page, $per_page, $offset, $sort_by, $sort_direction) {

            // restringindo parâmetros permitidos por segurança
            $this->sortingColumnsWhitelist = ['id', 'username', 'email', 'nickname', 'birthdate', 'creationdate'];
            $this->sortingDirectionsWhitelist = ['ASC', 'DESC'];

            // garantindo que os parametros estejam em limites razoáveis
            $this->page = max(1, (int)$page); // 1-n
            $this->perPageLimit = min(max(1, (int)$per_page), 150); // 1-150
            $this->offset = max(0, (int)$offset); // 0-n

            $this->sortByColumn = $sort_by;
            $this->sortDirection = $sort_direction;

            if(!in_array($sort_by, $this->sortingColumnsWhitelist)) $this->sortByColumn = 'id';
            if(!in_array(strtoupper($sort_direction), $this->sortingDirectionsWhitelist)) $this->sortDirection = 'ASC';

            $this->firstIndex = $this->perPageLimit * ($this->page - 1) + $this->offset;

        }

        public function getPage() {
            return $this->page;
        }

        public function getOffset() {
            return $this->offset;
        }

        public function getPerPageLimit() {
            return $this->perPageLimit;
        }

        public function getSortingColumn() {
            return $this->sortByColumn;
        }
        
        public function getSortingDirection() {
            return $this->sortDirection;
        }

        public function getFirstIndex() {
            return $this->firstIndex;
        }

    }

?>