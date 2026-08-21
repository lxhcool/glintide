<?php
if (!class_exists('CSF_Field_hidden')) {
    class CSF_Field_hidden extends CSF_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {

            echo $this->field_before();

            echo '<input type="hidden" name="' . $this->field_name() . '" value="' . $this->value . '"' . $this->field_attributes() . '  />';

            echo $this->field_after();
        }

    }
}