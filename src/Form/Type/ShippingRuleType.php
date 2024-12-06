<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);


namespace PrestaShop\Module\DemoProductForm\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;


class ShippingRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', CountryType::class, [
                'label' => 'Country',
            ])
            ->add('base_rate', NumberType::class, [
                'label' => 'Base Rate',
            ]);
        // $builder
        //     ->add('country', CountryType::class, [
        //         'label' => 'Destination Country',
        //         'placeholder' => 'Select a country',
        //     ])
        //     ->add('product_type', TextType::class, [
        //         'label' => 'Product Type',
        //         'attr' => [
        //             'placeholder' => 'e.g., Type X',
        //         ],
        //     ])
        //     ->add('base_rate', NumberType::class, [
        //         'label' => 'Base Rate',
        //         'attr' => [
        //             'placeholder' => 'e.g., $4.99',
        //         ],
        //     ])
        //     ->add('additional_rate', NumberType::class, [
        //         'label' => 'Additional Rate',
        //         'attr' => [
        //             'placeholder' => 'e.g., $2.00',
        //         ],
        //     ]);
    }
}
