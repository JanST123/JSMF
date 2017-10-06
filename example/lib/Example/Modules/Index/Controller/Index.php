<?php
namespace Example\Modules\Index\Controller;

class Index extends \JSMF\Controller {
  public function indexAction() {
    // this is the default action

    // do the application logic in the controllers or separate library classes, you can assign results to the view
    try {
      $db = \JSMF\Database::getInstance();
      $this->view->tickets = $db->selectAllRows(['title', 'body'])
                                ->from('tickets')
                                ->where([
           			  ['tracker', '=?'],
                                  'AND',
                                  ['visible', '=1'],
                                 ], [ $tracker ]
			        )
                                ->execute();

    } catch (\Exception $e) {
      // set dummy ticket data
      $this->view->tickets=[
        [
          'title' => 'Tickets could not be fetched from DB',
          'body' => 'You did either not created a sample ticket table or did not specify correct mysql credentials in the config',
        ],
	[
	  'title' => 'This is the exception',
          'body' => $e->getMessage(),
        ],
      ];
    }
    return $this->view;
  }

  public function jsonAction() {
    // an action can also return serializable data, which is then automatically printed as JSON. This actions does not need a view
    return [
     'Foo' => 'Bar',
    ];
  }
}


