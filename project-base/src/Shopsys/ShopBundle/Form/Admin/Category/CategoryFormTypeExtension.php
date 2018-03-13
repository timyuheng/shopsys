<?php

namespace Shopsys\ShopBundle\Form\Admin\Category;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;

class CategoryFormTypeExtension extends AbstractTypeExtension
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderSettingGroup = $builder->get('settings');
        $builderSettingGroup
            ->add('idExt', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter idExt']),
                ],
                'label' => t('ID Abra'),
                'icon_title' => t('Rosta prudic'),
            ]);

        $builder
            ->add($builderSettingGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CategoryFormType::class;
    }
}