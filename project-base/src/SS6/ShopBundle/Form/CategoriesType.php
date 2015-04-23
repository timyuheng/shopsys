<?php

namespace SS6\ShopBundle\Form;

use SS6\ShopBundle\Form\Extension\IndexedObjectChoiceList;
use SS6\ShopBundle\Model\Category\CategoryFacade;
use SS6\ShopBundle\Model\Category\Detail\CategoryDetailFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategoriesType extends AbstractType {

	/**
	 * @var \SS6\ShopBundle\Model\Category\CategoryFacade
	 */
	private $categoryFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Category\Detail\CategoryDetailFactory
	 */
	private $categoryDetailFactory;

	public function __construct(
		CategoryFacade $categoryFacade,
		CategoryDetailFactory $categoryDetailFactory
	) {
		$this->categoryFacade = $categoryFacade;
		$this->categoryDetailFactory = $categoryDetailFactory;
	}

	/**
	 * @param \Symfony\Component\Form\FormView $view
	 * @param \Symfony\Component\Form\FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['categoryDetails'] = $this->categoryDetailFactory->createDetailsHierarchy($options['choice_list']->getChoices());
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$categories = $this->categoryFacade->getAll();

		$resolver->setDefaults([
			'choice_list' => new IndexedObjectChoiceList($categories, 'id', 'name', [], null, 'id'),
			'multiple' => true,
			'expanded' => true,
		]);
	}

	/**
	 * @return string
	 */
	public function getParent() {
		return 'choice';
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'categories';
	}

}