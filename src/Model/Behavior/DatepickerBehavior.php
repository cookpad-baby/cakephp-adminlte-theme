<?php

namespace AdminLTE\Model\Behavior;

use \Cake\ORM\Behavior;
use \Cake\Event\Event;
use \Cake\ORM\Table;
use \ArrayObject;
use \Cake\Core\Configure;

class DatepickerBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'fields' => [],
        'date_separator' => '/',
    ];

    /**
     * Adding validation rules
     * also adds and merges config settings (direct + configure)
     *
     * @param \Cake\ORM\Table $table
     * @param array<string, mixed> $config
     */
    public function __construct(Table $table, array $config = [])
    {
        $config += $this->_defaultConfig;
        parent::__construct($table, $config);
    }

    /**
     * Preparing the data
     *
     * @param \Cake\Event\Event $event
     * @param \ArrayObject $data
     * @param \ArrayObject $options
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (!empty($this->_config) && !empty($this->_config['fields']) && is_array($this->_config['fields'])) {
            $separator = is_string($this->_config['date_separator']) && !empty($this->_config['date_separator']) ? $this->_config['date_separator'] : '/';
            $locale = Configure::read('App.defaultLocale');

            foreach ($this->_config['fields'] as $key) {
                if (isset($data[$key]) && is_string($data[$key])) {
                    if ($locale == 'pt_BR') {
                        list($d, $m, $y) = explode($separator, $data[$key]);
                    } else {
                        list($m, $d, $y) = explode($separator, $data[$key]);
                    }

                    $data[$key] = $y . '-' . $m . '-' . $d;
                }
            }
        }
    }
}