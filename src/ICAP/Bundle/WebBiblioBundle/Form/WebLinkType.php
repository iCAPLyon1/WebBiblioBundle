<?php

namespace ICAP\Bundle\WebBiblioBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WebLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'url')
            //->add('username', 'email')
            ->add('tags', 'icap_bundle_webbibliobundle_tagstype', array(
                'required'  => false,
            ))
            ->add('published', 'checkbox', array(
                'required'  => false,
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ICAP\Bundle\WebBiblioBundle\Entity\WebLink'
        ));
    }

    public function getName()
    {
        return 'icap_bundle_webbibliobundle_weblinktype';
    }
}
