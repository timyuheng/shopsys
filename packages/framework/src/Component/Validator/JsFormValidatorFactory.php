<?php

namespace Shopsys\FrameworkBundle\Component\Validator;

use Fp\JsFormValidatorBundle\Factory\JsFormValidatorFactory as BaseJsFormValidatorFactory;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints;

class JsFormValidatorFactory extends BaseJsFormValidatorFactory
{
    /**
     * @param array $constraints
     * @return array
     */
    protected function parseConstraints(array $constraints)
    {
        $result = parent::parseConstraints($constraints);

        foreach ($result as $items) {
            foreach ($items as $item) {
                if ($item instanceof Constraints\All) {
                    $item->constraints = $this->parseConstraints($item->constraints);
                }
            }
        }

        return $result;
    }

    /**
     * @param FormInterface $form
     * @param array $viewTransformers
     *
     * @return array
     */
    protected function normalizeViewTransformers(FormInterface $form, array $viewTransformers)
    {
        $config = $form->getConfig();

        // Choice(s)ToBooleanArrayTransformer was deprecated in SF2.7 in favor of CheckboxListMapper and RadioListMapper
        if ($config->getType()->getInnerType() instanceof ChoiceType && $config->getOption('expanded')) {
            $namespace = 'Symfony\Component\Form\Extension\Core\DataTransformer\\';
            $transformer = $config->getOption('multiple')
                ? ['name' => $namespace . 'ChoicesToBooleanArrayTransformer']
                : ['name' => $namespace . 'ChoiceToBooleanArrayTransformer'];

            $transformer['choiceList'] = [];
            foreach ($config->getOption('choices') as $formOptionChoiceItem) {
                if (preg_match('#^/admin/#', $this->router->getContext()->getPathInfo()) !== 1
                    && (
                        $formOptionChoiceItem instanceof ParameterValue
                        || $formOptionChoiceItem instanceof Flag
                        || $formOptionChoiceItem instanceof Brand
                    )
                ) {
                    $optionItemId = $formOptionChoiceItem->getId();
                    $transformer['choiceList'][$optionItemId] = $formOptionChoiceItem;
                } else {
                    $transformer['choiceList'][] = $formOptionChoiceItem;
                }
            }

            array_unshift($viewTransformers, $transformer);
        }

        return $viewTransformers;
    }
}
