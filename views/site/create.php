<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\date\DatePickerAsset;

$this->title = 'Dashboard';
?>
<div class="row">

    <div class="panel">
        <div class="panel-heading">
            <span class="panel-title">
                Page Title
            </span>
        </div>
        <hr class="no-margin-bottom">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="control-label">
                            Form 1 Title
                        </label>
                        <input class="form-control" type="text">
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            Form 1 Title
                        </label>
                        <select class="form-control">
                            <option>
                                Select an option
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            Form Radio
                        </label>
                        <div class="radio">
                            <label class="radio-inline">
                                <input type="radio"> Radio One
                            </label>
                            <label class="radio-inline">
                                <input type="radio"> Radio Two
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label"> Date Picker</label>
                        <?= DatePicker::widget([
                            'name' => 'check_issue_date',
                            'value' => date('d-M-Y', strtotime('+2 days')),
                            'options' => ['placeholder' => 'Select issue date ...' , 'id' => 'datePicker'],
                            'pluginOptions' => [
                                'format' => 'dd-M-yyyy',
                                'todayHighlight' => true
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>