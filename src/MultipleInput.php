<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace unclead\widgets;

use Yii;
use yii\base\Model;
use yii\widgets\InputWidget;
use yii\db\ActiveRecord;
use unclead\widgets\renderers\TableRenderer;


/**
 * Widget for rendering multiple input for an attribute of model.
 *
 * @author Eugene Tupikov <unclead.nsk@gmail.com>
 */
class MultipleInput extends InputWidget
{
    /**
     * @var ActiveRecord[]|array[] input data
     */
    public $data = null;

    /**
     * @var array columns configuration
     */
    public $columns = [];

    /**
     * @var integer inputs limit
     */
    public $limit;

    /**
     * @var integer inputs minimal number
     */
    public $min;

    /**
     * @var array client-side attribute options, e.g. enableAjaxValidation. You may use this property in case when
     * you use widget without a model, since in this case widget is not able to detect client-side options
     * automatically.
     */
    public $attributeOptions = [];

    /**
     * @var array the HTML options for the `remove` button
     */
    public $removeButtonOptions;

    /**
     * @var array the HTML options for the `add` button
     */
    public $addButtonOptions;

    /**
     * @var bool whether to allow the empty list
     */
    public $allowEmptyList = false;

    /**
     * @var bool whether to guess column title in case if there is no definition of columns
     */
    public $enableGuessTitle = false;

    /**
     * @var array table row HTML options
     */
    public $rowOptions = "";

    /**
     * Initialization.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->guessColumns();
        $this->initData();
        parent::init();
    }

    /**
     * Initializes data.
     */
    protected function initData()
    {
        if (is_null($this->data) && $this->model instanceof Model) {
            foreach ((array) $this->model->{$this->attribute} as $index => $value) {
                $this->data[$index] = $value;
            }
        }
    }

    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     */
    protected function guessColumns()
    {
        if (empty($this->columns) && $this->hasModel()) {
            $column = [
                'name' => $this->attribute,
                'type' => MultipleInputColumn::TYPE_TEXT_INPUT
            ];

            if ($this->enableGuessTitle && $this->hasModel()) {
                $column['title'] = $this->model->getAttributeLabel($this->attribute);
            }
            $this->columns[] = $column;
        }
    }

    /**
     * Run widget.
     */
    public function run()
    {
        return $this->createRenderer()->render();
    }

    /**
     * @return TableRenderer
     */
    private function createRenderer()
    {
        $config = [
            'id'                => $this->options['id'],
            'columns'           => $this->columns,
            'limit'             => $this->limit,
            'min'               => $this->min,
            'attributeOptions'  => $this->attributeOptions,
            'data'              => $this->data,
            'columnClass'       => MultipleInputColumn::className(),
            'allowEmptyList'    => $this->allowEmptyList,
            'context'           => $this,
            'rowOptions'        => $this->rowOptions,
        ];

        if (!is_null($this->removeButtonOptions)) {
            $config['removeButtonOptions'] = $this->removeButtonOptions;
        }

        if (!is_null($this->addButtonOptions)) {
            $config['addButtonOptions'] = $this->addButtonOptions;
        }

        return new TableRenderer($config);
    }
}