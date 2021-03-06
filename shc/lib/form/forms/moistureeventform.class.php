<?php

namespace SHC\Form\Forms;

//Imports
use RWF\Core\RWF;
use RWF\Form\DefaultHtmlForm;
use RWF\Form\FormElements\FloatInputField;
use RWF\Form\FormElements\IntegerInputField;
use RWF\Form\FormElements\OnOffOption;
use RWF\Form\FormElements\TextField;
use SHC\Event\AbstractEvent;
use SHC\Event\Events\MoistureClimbOver;
use SHC\Event\Events\MoistureFallsBelow;
use SHC\Form\FormElements\ConditionsChooser;
use SHC\Form\FormElements\SensorChooser;

/**
 * Feuchtigkeit Ereignis
 *
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class MoistureEventForm extends DefaultHtmlForm {

    /**
     * @param AbstractEvent $condition
     */
    public function __construct(AbstractEvent $event = null) {

        //Pruefen ob zulaessiges Objekt uebergeben
        if($event !== null && !$event instanceof MoistureClimbOver && !$event instanceof MoistureFallsBelow) {

            throw new \Exception('ungültiges Ereignis übergeben', 1513);
        }

        //Konstruktor von TabbedHtmlForm aufrufen
        parent::__construct();

        RWF::getLanguage()->disableAutoHtmlEndocde();

        //Name der Aktivitaet
        $name = new TextField('name', ($event !== null ? $event->getName() : ''), array('minlength' => 3, 'maxlength' => 25));
        $name->setTitle(RWF::getLanguage()->get('acp.eventsManagement.form.event.name'));
        $name->setDescription(RWF::getLanguage()->get('acp.eventsManagement.form.event.name.description'));
        $name->requiredField(true);
        $this->addFormElement($name);

        //Bedingungen
        $conditions = new ConditionsChooser('conditions', ($event !== null ? $event->listConditions() : array()));
        $conditions->setTitle(RWF::getLanguage()->get('acp.eventsManagement.form.event.condition'));
        $conditions->setDescription(RWF::getLanguage()->get('acp.eventsManagement.form.event.condition.decription'));
        $conditions->requiredField(true);
        $this->addFormElement($conditions);

        //Sensoren
        $sensors = new SensorChooser('sensors', ($event !== null ? explode(',', $event->getData()['sensors']) : array()), SensorChooser::MOISTURE);
        $sensors->setTitle(RWF::getLanguage()->get('acp.eventsManagement.form.event.sensors'));
        $sensors->setDescription(RWF::getLanguage()->get('acp.eventsManagement.form.event.sensors.description'));
        $sensors->requiredField(true);
        $this->addFormElement($sensors);

        //Grenzwert
        $humidity = new FloatInputField('limit', ($event !== null ? (float) $event->getData()['limit'] : 50.0), array('min' => 0, 'max' => 100, 'step' => 0.5));
        $humidity->setTitle(RWF::getLanguage()->get('acp.eventsManagement.form.event.limit'));
        $humidity->setDescription(RWF::getLanguage()->get('acp.eventsManagement.form.event.mouistureLimit.description'));
        $humidity->requiredField(true);
        $this->addFormElement($humidity);

        //Intervall
        $name = new IntegerInputField('interval', ($event !== null ? $event->getData()['interval'] : 30), array('min' => 10, 'max' => 3600));
        $name->setTitle(RWF::getLanguage()->get('acp.eventsManagement.form.event.interval'));
        $name->setDescription(RWF::getLanguage()->get('acp.eventsManagement.form.event.interval.description'));
        $name->requiredField(true);
        $this->addFormElement($name);

        //Aktiv/Inaktiv
        $enabled = new OnOffOption('enabled', ($event !== null ? $event->isEnabled() : true));
        $enabled->setActiveInactiveLabel();
        $enabled->setTitle(RWF::getLanguage()->get('acp.eventsManagement.form.event.active'));
        $enabled->setDescription(RWF::getLanguage()->get('acp.eventsManagement.form.event.active.description'));
        $enabled->requiredField(true);
        $this->addFormElement($enabled);

        RWF::getLanguage()->enableAutoHtmlEndocde();
    }
}