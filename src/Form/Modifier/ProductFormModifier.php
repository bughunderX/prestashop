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

namespace PrestaShop\Module\DemoProductForm\Form\Modifier;

use PrestaShop\Module\DemoProductForm\CQRS\CommandHandler\UpdateCustomProductCommandHandler;
use PrestaShop\Module\DemoProductForm\Entity\CustomProduct;
use PrestaShop\Module\DemoProductForm\Entity\ShippingRule;
use PrestaShop\Module\DemoProductForm\Form\Type\CustomTabType;
use PrestaShop\Module\DemoProductForm\Form\Type\ShippingRuleType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShopBundle\Form\Admin\Type\IconButtonType;
use PrestaShopBundle\Form\FormBuilderModifier;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class ProductFormModifier
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormBuilderModifier
     */
    private $formBuilderModifier;

    /**
     * @param TranslatorInterface $translator
     * @param FormBuilderModifier $formBuilderModifier
     */
    public function __construct(
        TranslatorInterface $translator,
        FormBuilderModifier $formBuilderModifier
    ) {
        $this->translator = $translator;
        $this->formBuilderModifier = $formBuilderModifier;
    }

    /**
     * @param ProductId|null $productId
     * @param FormBuilderInterface $productFormBuilder
     */
    public function modify(
        ?ProductId $productId,
        FormBuilderInterface $productFormBuilder,
        ShippingRule $shippingRules
    ): void {
        $idValue = $productId ? $productId->getValue() : null;
        $customProduct = new CustomProduct($idValue);
        $this->modifyDescriptionTab($customProduct, $productFormBuilder, $shippingRules);
        $this->addCustomTab($customProduct, $productFormBuilder);
        $this->modifyFooter($productFormBuilder);
    }

    /**
     * @param CustomProduct $customProduct
     * @param FormBuilderInterface $productFormBuilder
     *
     * @see UpdateCustomProductCommandHandler to check how the field is handled on form POST
     */
    // private function modifyDescriptionTab(CustomProduct $customProduct, FormBuilderInterface $productFormBuilder): void
    // {
    //     $descriptionTabFormBuilder = $productFormBuilder->get('shipping');
    //     $productFormBuilder -> remove('additional_shipping_cost');
        
    //     $this->formBuilderModifier->addAfter(
    //         $descriptionTabFormBuilder,
    //         'additional_shipping_cost',
    //         'shipping_country',
    //         CountryType::class,
    //         [
    //             'label' => 'Country', // Label for the first field
    //             'label_attr' => [
    //                 'class' => 'col-form-label text-info',
    //             ],
    //             'attr' => [
    //                 'class' => 'form-control col-6', // Make it take the other half of the row
    //                 'placeholder' => 'Select Country',
    //             ],
    //             'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit_base.html.twig',
    //         ]
    //     );
        
    //     $this->formBuilderModifier->addAfter(
    //         $descriptionTabFormBuilder,
    //         'shipping_country',
    //         'shipping_start_rate',
    //         TextType::class,
    //         [
    //             'label' => 'Start Rate', // Label for the second field
    //             'label_attr' => [
    //                 'class' => 'col-form-label text-info',
    //             ],
    //             'attr' => [
    //                 'class' => 'form-control col-6', // Make it take the other half of the row
    //                 'placeholder' => '0',
    //             ],
    //             'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit_base.html.twig',
    //             'required' => true,
    //         ]
    //     );
        
    //     $this->formBuilderModifier->addAfter(
    //         $descriptionTabFormBuilder,
    //         'shipping_start_rate',
    //         'shipping_extra_rate',
    //         TextType::class,
    //         [
    //             'label' => 'Extra Rate', // Label for the second field
    //             'label_attr' => [
    //                 'class' => 'col-form-label text-info',
    //             ],
    //             'attr' => [
    //                 'class' => 'form-control col-6', // Make it take the other half of the row
    //                 'placeholder' => '',
    //             ],
    //             'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit_base.html.twig',
    //             'required' => true,
    //         ]
    //     );
    // }
    private function modifyDescriptionTab(CustomProduct $customProduct, FormBuilderInterface $productFormBuilder, ShippingRule $shippingRule): void
    {
        // Fetch existing shipping rules data for the current product
    
        // If we have shipping rules, pre-populate the form fields
        $shippingCountry = $shippingRule->shipping_country;
        $shippingStartRate = $shippingRule->shipping_start_rate;
        $shippingExtraRate = $shippingRule->shipping_extra_rate;
    
        // Modify the form builder
        $descriptionTabFormBuilder = $productFormBuilder->get('shipping');
        $productFormBuilder->remove('additional_shipping_cost');
    
        $this->formBuilderModifier->addAfter(
            $descriptionTabFormBuilder,
            'additional_shipping_cost',
            'shipping_country',
            CountryType::class,
            [
                'label' => 'Country', 
                'label_attr' => ['class' => 'col-form-label text-info'],
                'attr' => ['class' => 'form-control col-6', 'placeholder' => 'Select Country'],
                'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit_base.html.twig',
                'data' => $shippingCountry,  // Set the pre-populated value here
            ]
        );
    
        $this->formBuilderModifier->addAfter(
            $descriptionTabFormBuilder,
            'shipping_country',
            'shipping_start_rate',
            TextType::class,
            [
                'label' => 'Start Rate', 
                'label_attr' => ['class' => 'col-form-label text-info'],
                'attr' => ['class' => 'form-control col-6', 'placeholder' => '0'],
                'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit_base.html.twig',
                'required' => true,
                'data' => $shippingStartRate,  // Set the pre-populated value here
            ]
        );
    
        $this->formBuilderModifier->addAfter(
            $descriptionTabFormBuilder,
            'shipping_start_rate',
            'shipping_extra_rate',
            TextType::class,
            [
                'label' => 'Extra Rate', 
                'label_attr' => ['class' => 'col-form-label text-info'],
                'attr' => ['class' => 'form-control col-6', 'placeholder' => ''],
                'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit_base.html.twig',
                'required' => true,
                'data' => $shippingExtraRate,  // Set the pre-populated value here
            ]
        );
    }
    
    /**
     * @param CustomProduct $customProduct
     * @param FormBuilderInterface $productFormBuilder
     */
    private function addCustomTab(CustomProduct $customProduct, FormBuilderInterface $productFormBuilder): void
    {
        $this->formBuilderModifier->addAfter(
            $productFormBuilder,
            'pricing',
            'custom_tab',
            CustomTabType::class,
            [
                'data' => [
                    'custom_price' => $customProduct->custom_price,
                ],
            ]
        );
    }

    /**
     * @param FormBuilderInterface $productFormBuilder
     */
    private function modifyFooter(FormBuilderInterface $productFormBuilder): void
    {
        $headerFormBuilder = $productFormBuilder->get('footer');
        $headerFormBuilder->add('forms_info', IconButtonType::class, [
            'label' => $this->translator->trans('Open supplier website'),
            'type' => 'link',
            'attr' => [
                'href' => 'http://www.prestashop.com',
                'target' => '_blank',
            ],
        ]);
    }
}
