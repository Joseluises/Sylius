<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\AttributeBundle\Controller;

use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mateusz Zalewski <mateusz.zalewski@lakion.com>
 */
class AttributeController extends ResourceController
{
    /**
     * @param Request $request
     * @param string $template
     *
     * @return Response
     */
    public function getAttributeTypesAction(Request $request, $template)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $view = View::create()
            ->setTemplate($template)
            ->setTemplateVar($this->metadata->getPluralName())
            ->setData(array('attributeTypes' => $this->get('sylius.registry.attribute_type')->all()))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * @return Response
     */
    public function renderAttributesAction()
    {
        $form = $this->get('form.factory')->create(
            sprintf('sylius_%s_choice', $this->config->getResourceName()),
            null,
            array(
                'expanded' => true,
                'multiple' => true,
            )
        );

        return $this->render('SyliusAttributeBundle::attributeChoice.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function renderAttributeValueFormsAction(Request $request)
    {
        $attributeRepository = $this->get('sylius.repository.'.$this->config->getResourceName());
        $forms = array();

        $choices = $request->query->get(sprintf('sylius_%s_choice', $this->config->getResourceName()), array());

        $attributes = $attributeRepository->findBy(array('id' => $choices));
        foreach ($attributes as $attribute) {
            $attributeForm = 'sylius_attribute_type_'.$attribute->getType();

            $options = array('label' => $attribute->getName());

            $form = $this->get('form.factory')->createNamed('value', $attributeForm, null, $options);
            $forms[$attribute->getId()] = $form->createView();
        }

        return $this->render('SyliusAttributeBundle::attributeValueForms.html.twig', array('forms' => $forms, 'count' => $request->query->get('count')));
    }
}
