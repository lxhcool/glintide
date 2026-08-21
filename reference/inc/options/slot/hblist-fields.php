<?php
if (!class_exists('CSF_Field_hblist')) {
    class CSF_Field_hblist extends CSF_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {
            
            $prefix = explode('[', $this->unique)[0];
            //var_dump($this->field);
            echo $this->field_before();         
            echo '<div id="ppo-builder-list-box" class="ppo-builder-list-box">';
            do_action('ppo_header_builder_item', array('id' => $this->field['id'],'value' => $this->value, 'prefix' => $prefix));
            echo '</div>';
            echo $this->field_after();
        }

    }
}