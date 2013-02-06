<?php

namespace ICAP\Bundle\WebBiblioBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use ICAP\Bundle\WebBiblioBundle\Form\DataTransformer\TagsToTextTransformer;

class TagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->appendClientTransformer(new TagsToTextTransformer());
    }
        
    public function getParent()
    {
        return 'text';
    }
 
    public function getName()
    {
        return 'icap_bundle_webbibliobundle_tagstype';
    }
}