<?php

namespace ICAP\Bundle\WebBiblioBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ICAP\Bundle\WebBiblioBundle\Entity\Tag'
        ));
    }

    public function getName()
    {
        return 'icap_bundle_webbibliobundle_tagtype';
    }
}
