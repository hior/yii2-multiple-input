<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace unclead\widgets\renderers;

use yii\base\InvalidConfigException;
use yii\bootstrap\Button;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use unclead\widgets\components\BaseRenderer;
use unclead\widgets\components\BaseColumn;

/**
 * Class TableRenderer
 * @package unclead\widgets\renderers
 */
class TableRenderer extends BaseRenderer
{
    /**
     * @return mixed
     */
    protected function internalRender()
    {
        $content = [];

        if ($this->hasHeader()) {
            $content[] = $this->renderHeader();
        }

        $content[] = $this->renderBody();
        $content = Html::tag('table', implode("\n", $content), [
            'class' => 'multiple-input-list table table-condensed'
        ]);

        return Html::tag( 'div', $content, [
            'id' => $this->id,
            'class' => 'multiple-input'
        ]);
    }

    /**
     * Renders the header.
     *
     * @return string
     */
    public function renderHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            $cells[] = $this->renderHeaderCell($column);
        }
        if (is_null($this->limit) || $this->limit > 1) {
            $button = $this->allowEmptyList ? $this->renderAddButton() : '';
            $cells[] = Html::tag('th', $button, [
                'class' => 'list-cell__button'
            ]);
        }

        return Html::tag('thead', Html::tag('tr', implode("\n", $cells)));
    }

    /**
     * Check that at least one column has a header.
     *
     * @return bool
     */
    private function hasHeader()
    {
        if ($this->allowEmptyList) {
            return true;
        }
        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            if (!empty($column->title)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Renders the header cell.
     * @param BaseColumn $column
     * @return null|string
     */
    private function renderHeaderCell($column)
    {
        if ($column->isHiddenInput()) {
            return null;
        }
        $options = $column->headerOptions;
        Html::addCssClass($options, 'list-cell__' . $column->name);
        return Html::tag('th', $column->title, $options);
    }

    /**
     * Renders the body.
     *
     * @return string
     */
    protected function renderBody()
    {
        $rows = [];

        if (!empty($this->data)) {
            foreach ($this->data as $index => $item) {
                $rows[] = $this->renderRowContent($index, $item);
            }
        } else {
            $rows[] = $this->renderRowContent(0);
        }
        return Html::tag('tbody', implode("\n", $rows));
    }

    /**
     * Renders the row content.
     *
     * @param int $index
     * @param ActiveRecord|array $item
     * @return mixed
     * @throws InvalidConfigException
     */
    private function renderRowContent($index = null, $item = null)
    {
        $cells = [];
        $hiddenInputs = [];
        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            $column->setModel($item);
            if ($column->isHiddenInput()) {
                $hiddenInputs[] = $this->renderCellContent($column, $index);
            } else {
                $cells[] = $this->renderCellContent($column, $index);
            }
        }

        if (is_null($this->limit) || $this->limit > 1) {
            $cells[] = $this->renderActionColumn($index);
        }

        if (!empty($hiddenInputs)) {
            $hiddenInputs = implode("\n", $hiddenInputs);
            $cells[0] = preg_replace('/^(<td[^>]+>)(.*)(<\/td>)$/s', '${1}' . $hiddenInputs . '$2$3', $cells[0]);
        }

        $content = Html::tag('tr', implode("\n", $cells), [
            'class' => 'multiple-input-list__item',
        ]);

        return $content;
    }

    /**
     * Renders the cell content.
     *
     * @param BaseColumn $column
     * @param int|null $index
     * @return string
     */
    public function renderCellContent($column, $index)
    {
        $id    = $column->getElementId($index);
        $name  = $column->getElementName($index);
        $input = $column->renderInput($name, [
            'id' => $id
        ]);

        if ($column->isHiddenInput()) {
            return $input;
        }

        $hasError = false;
        if ($column->enableError) {
            $error = $column->getFirstError($index);
            $hasError = !empty($error);
            $input .= "\n" . $column->renderError($error);
        }

        $wrapperOptions = [
            'class' => 'form-group field-' . $id
        ];

        if ($hasError) {
            Html::addCssClass($wrapperOptions, 'has-error');
        }
        $input = Html::tag('div', $input, $wrapperOptions);

        return Html::tag('td', $input, [
            'class' => 'list-cell__' . $column->name,
        ]);
    }


    /**
     * Renders the action column.
     *
     * @param null|int $index
     * @return string
     * @throws \Exception
     */
    private function renderActionColumn($index = null)
    {
        if (is_null($index)) {
            $button = $this->renderRemoveButton();
        } elseif ($index == 0) {
            $button = $this->allowEmptyList ? $this->renderRemoveButton() : $this->renderAddButton();
        } else {
            $button = $this->renderRemoveButton();
        }

        return Html::tag('td', $button, [
            'class' => 'list-cell__button',
        ]);
    }

    private function renderAddButton()
    {
        $options = [
            'class' => 'multiple-input-list__btn js-input-plus',
        ];
        Html::addCssClass($options, $this->addButtonOptions['class']);
        return Button::widget(
            [
                'tagName'       => 'div',
                'encodeLabel'   => false,
                'label'         => $this->addButtonOptions['label'],
                'options'       => $options
            ]
        );
    }

    /**
     * Renders remove button.
     *
     * @return string
     * @throws \Exception
     */
    private function renderRemoveButton()
    {
        $options = [
            'class' => 'multiple-input-list__btn js-input-remove',
        ];
        Html::addCssClass($options, $this->removeButtonOptions['class']);
        return Button::widget(
            [
                'tagName'       => 'div',
                'encodeLabel'   => false,
                'label'         => $this->removeButtonOptions['label'],
                'options'       => $options
            ]
        );
    }

    /**
     * Returns template for using in js.
     *
     * @return string
     */
    protected function prepareTemplate()
    {
        return $this->renderRowContent();
    }
}