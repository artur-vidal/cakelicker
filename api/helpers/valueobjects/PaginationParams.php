<?php

    namespace Cakelicker\ValueObjects;

    class PaginationParams {
        
        private $page = 1;
        private $offset = 0;
        private $perPageLimit = 10;
        private $sortColumn = 'id';
        private $sortDirection = 'ASC';

        private $sortingColumnsWhitelist = ['id', 'username', 'email', 'nickname', 'birthdate', 'creationdate'];
        private $sortingDirectionsWhitelist = ['ASC', 'DESC'];

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
            return $this->sortColumn;
        }
        
        public function getSortingDirection() {
            return $this->sortDirection;
        }

        public function getFirstIndex() {
            return $this->perPageLimit * ($this->page - 1) + $this->offset;
        }

        public function setPage($page) {
            $this->page = max(1, (int)$page); // 1-n
            return $this;
        }

        public function setPerPageLimit($per_page) {
            $this->perPageLimit = min(max(1, (int)$per_page), 150); // 1-150
            return $this;
        }

        public function setOffset($offset) {
            $this->offset = max(0, (int)$offset); // 0-n
            return $this;
        }

        public function setSortColumn($sort_by) {
            if(in_array($sort_by, $this->sortingColumnsWhitelist))
                $this->sortColumn = $sort_by;
            else
                $this->sortColumn = 'id';
            
            return $this;
        }

        public function setSortDirection($sort_direction) {
            if(in_array($sort_direction, $this->sortingDirectionsWhitelist))
                $this->sortDirection = $sort_direction;
            else
                $this->sortDirection = 'ASC';

            return $this;
        }

    }

?>