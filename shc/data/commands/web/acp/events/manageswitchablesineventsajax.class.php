<?php

namespace SHC\Command\Web;

//Imports
use RWF\Core\RWF;
use RWF\Form\FormElements\Select;
use RWF\Request\Commands\AjaxCommand;
use RWF\Request\Request;
use RWF\Util\DataTypeUtil;
use RWF\Util\Message;
use SHC\Event\Event;
use SHC\Event\EventEditor;
use SHC\Form\FormElements\SwitchCommandChooser;
use SHC\Switchable\AbstractSwitchable;
use SHC\Switchable\Switchable;
use SHC\Switchable\SwitchableEditor;
use SHC\Switchable\Switchables\Activity;
use SHC\Switchable\Switchables\ArduinoOutput;
use SHC\Switchable\Switchables\Countdown;
use SHC\Switchable\Switchables\RadioSocket;
use SHC\Switchable\Switchables\RpiGpioOutput;
use SHC\Switchable\Switchables\WakeOnLan;

/**
 * verwaltet die Elemente von Ereignissen
 *
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class ManageSwitchablesInEventsAjax extends AjaxCommand {

    protected $premission = 'shc.acp.eventsManagement';

    /**
     * Sprachpakete die geladen werden sollen
     *
     * @var Array
     */
    protected $languageModules = array('switchablemanagement', 'eventmanagement', 'acpindex', 'form');

    /**
     * Daten verarbeiten
     */
    public function processData() {

        //Template Objekt holen
        $tpl = RWF::getTemplate();

        //Ereignis Objekt laden
        $eventId = RWF::getRequest()->getParam('id', Request::GET, DataTypeUtil::INTEGER);
        $event = EventEditor::getInstance()->getEventById($eventId);

        if($event instanceof Event) {

            if(RWF::getRequest()->issetParam('command', Request::GET)) {

                //Befehl
                $command = RWF::getRequest()->getParam('command', Request::GET, DataTypeUtil::STRING);

                $message = new Message();
                if($command == 'addElement') {

                    //element hinzufuegen
                    $switchableElementId = RWF::getRequest()->getParam('element', Request::GET, DataTypeUtil::INTEGER);
                    $switchCommand = RWF::getRequest()->getParam('switchCommand', Request::GET, DataTypeUtil::INTEGER);

                    //Eingaben pruefen
                    $error = false;
                    $switchableElementObject = SwitchableEditor::getInstance()->getElementById($switchableElementId);
                    if (!$switchableElementObject instanceof Switchable) {

                        $message->setType(Message::ERROR);
                        $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.error.id'));
                        $error = true;
                    }

                    if (!in_array($switchCommand, array('0', '1'))) {

                        $message->setType(Message::ERROR);
                        $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.error.command'));
                        $error = true;
                    }

                    //Element hinzufuegen
                    if ($error === false) {

                        try {

                            //Speichern
                            EventEditor::getInstance()->addSwitchableToEvent($eventId, $switchableElementId,$switchCommand);
                            $message->setType(Message::SUCCESSFULLY);
                            $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.success'));
                            EventEditor::getInstance()->loadData();
                            $event = EventEditor::getInstance()->getEventById($eventId);
                        } catch (\Exception $e) {

                            if($e->getCode() == 1102) {

                                //fehlende Schreibrechte
                                $message->setType(Message::ERROR);
                                $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.error.1102'));
                            } else {

                                //Allgemeiner Fehler
                                $message->setType(Message::ERROR);
                                $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.error'));
                            }
                        }
                    }
                } elseif($command == 'toggle') {

                    //Befehl umkehren
                    $switchableElementId = RWF::getRequest()->getParam('element', Request::GET, DataTypeUtil::INTEGER);

                    //Eingaben pruefen
                    $error = false;
                    $switchableElementObject = SwitchableEditor::getInstance()->getElementById($switchableElementId);
                    if (!$switchableElementObject instanceof Switchable) {

                        $message->setType(Message::ERROR);
                        $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.error.id'));
                        $error = true;
                    }

                    //Element hinzufuegen
                    if ($error === false) {

                        try {

                            //Speichern
                            $newCommand = AbstractSwitchable::STATE_OFF;
                            foreach($event->listSwitchables() as $switchable) {

                                if($switchable['object'] == $switchableElementObject) {

                                    if($switchable['command'] == AbstractSwitchable::STATE_ON) {

                                        $newCommand = AbstractSwitchable::STATE_OFF;
                                    } else {

                                        $newCommand = AbstractSwitchable::STATE_ON;
                                    }
                                }
                            }
                            EventEditor::getInstance()->setEventSwitchableCommand($eventId, $switchableElementId, $newCommand);
                            $message->setType(Message::SUCCESSFULLY);
                            $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.success'));
                            EventEditor::getInstance()->loadData();
                            $event = EventEditor::getInstance()->getEventById($eventId);
                        } catch (\Exception $e) {

                            if($e->getCode() == 1102) {

                                //fehlende Schreibrechte
                                $message->setType(Message::ERROR);
                                $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.error.1102'));
                            } else {

                                //Allgemeiner Fehler
                                $message->setType(Message::ERROR);
                                $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.error'));
                            }
                        }
                    }
                } elseif($command = 'delete') {

                    //element loeschen
                    $switchableElementId = RWF::getRequest()->getParam('element', Request::GET, DataTypeUtil::INTEGER);

                    //Eingaben pruefen
                    $error = false;
                    $switchableElementObject = SwitchableEditor::getInstance()->getElementById($switchableElementId);
                    if (!$switchableElementObject instanceof Switchable) {

                        $message->setType(Message::ERROR);
                        $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.error.id'));
                        $error = true;
                    }

                    //Element hinzufuegen
                    if ($error === false) {

                        try {

                            //loeschen
                            EventEditor::getInstance()->removeSwitchableFromEvent($eventId, $switchableElementId);
                            $message->setType(Message::SUCCESSFULLY);
                            $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.removeSuccesss'));
                            EventEditor::getInstance()->loadData();
                            $event = EventEditor::getInstance()->getEventById($eventId);
                        } catch (\Exception $e) {

                            if($e->getCode() == 1102) {

                                //fehlende Schreibrechte
                                $message->setType(Message::ERROR);
                                $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.removeError.1102'));
                            } else {

                                //Allgemeiner Fehler
                                $message->setType(Message::ERROR);
                                $message->setMessage(RWF::getLanguage()->get('acp.eventsManagement.form.addElement.removeError'));
                            }
                        }
                    }
                }

                $tpl->assign('message', $message);
            }

            //Formularfelder erstellen
            $elementChooser = new Select('element');
            $values = array();
            foreach(SwitchableEditor::getInstance()->listElements(SwitchableEditor::SORT_BY_NAME) as $switchableElement) {

                if($switchableElement instanceof Switchable) {

                    //pruefen ob Element schon registriert
                    $found = false;
                    foreach($event->listSwitchables() as $switchable) {

                        if($switchable['object'] == $switchableElement) {

                            $found = true;
                            break;
                        }
                    }
                    if($found == true) {

                        //wenn schon registriert Element ueberspringen
                        continue;
                    }

                    $type = '';
                    RWF::getLanguage()->disableAutoHtmlEndocde();
                    if($switchableElement instanceof ArduinoOutput) {

                        $type = RWF::getLanguage()->get('acp.switchableManagement.element.arduinoOutput');
                    } elseif($switchableElement instanceof RadioSocket) {

                        $type = RWF::getLanguage()->get('acp.switchableManagement.element.radiosocket');
                    } elseif($switchableElement instanceof RpiGpioOutput) {

                        $type = RWF::getLanguage()->get('acp.switchableManagement.element.rpiGpioOutput');
                    } elseif($switchableElement instanceof WakeOnLan) {

                        $type = RWF::getLanguage()->get('acp.switchableManagement.element.wakeOnLan');
                    } elseif($switchableElement instanceof Activity) {

                        $type = RWF::getLanguage()->get('acp.switchableManagement.element.activity');
                    } elseif($switchableElement instanceof Countdown) {

                        $type = RWF::getLanguage()->get('acp.switchableManagement.element.countdown');
                    }
                    RWF::getLanguage()->enableAutoHtmlEndocde();
                    $values[$switchableElement->getId()] = $switchableElement->getName() .' ('. $type .')';
                }
            }
            $elementChooser->setValues($values);

            //Schaltbefehl
            $switchCommand = new SwitchCommandChooser('switchCommand');

            //Elemente Liste Template Anzeigen
            $tpl->assign('event', $event);
            $tpl->assign('elementChooser', $elementChooser);
            $tpl->assign('switchCommand', $switchCommand);
            $tpl->assign('elementList', $event->listSwitchables());
            $this->data = $tpl->fetchString('manageswitchablesinevents.html');
        } else {

            //Ungueltige ID
            $tpl->assign('message', new Message(Message::ERROR, RWF::getLanguage()->get('acp.eventsManagement.form.error.id')));
            $this->data = $tpl->fetchString('manageswitchablesinevents.html');
            return;
        }
    }
}