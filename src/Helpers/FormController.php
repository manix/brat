<?php

namespace Manix\Brat\Helpers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;

abstract class FormController extends Controller {

  protected $form;
  protected $rules;

  protected final function getForm(): Form {
    if ($this->form === null) {
      $this->form = $this->constructForm(new Form);
    }

    return $this->form;
  }

  protected final function getRules(): Ruleset {
    if ($this->rules === null) {
      $this->rules = $this->constructRules(new Ruleset);
    }

    return $this->rules;
  }

  /**
   * Runs $dataset through a validator using the ruleset constructed in 
   * constructRules() and returns the return value of $onPass or $onFail respectively.
   * 
   * @param mixed $dataset Iterable dataset.
   * @param \callable $onPass
   * @param \callable $onFail
   * @return mixed The return value of $onPass or $onFail.
   */
  protected function validate($dataset, callable $onPass = null, callable $onFail = null, ...$data) {
    $validator = new Validator();
    $rules = $this->getRules();

    if ($validator->validate($dataset, $rules)) {
      return $onPass($dataset, $validator, $rules, ...$data);
    } else {
      if ($onFail === null) {
        return $this->defaultFailAction($dataset, $validator, $rules, ...$data);
      } else {
        return $onFail($dataset, $validator, $rules, ...$data);
      }
    }
  }

  protected function defaultFailAction($data, $validator) {
    $this->getForm()->fill($data)->errors = $validator->getErrors();

    return $this->get();
  }

  abstract protected function constructForm(Form $form): Form;

  abstract protected function constructRules(Ruleset $rules): Ruleset;
}
