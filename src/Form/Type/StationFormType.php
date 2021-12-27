<?php


namespace App\Form\Type;


use App\Entity\Station;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class StationFormType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * StationFormType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [ 'required' => true, 'label' => $this->translator->trans('Sendername') ])
            ->add('url', TextType::class, [ 'required' => true, 'label' => $this->translator->trans('Senderadresse') ])
            ->add('logo', FileType::class, [ 'mapped' => false, 'required' => false, 'label' => $this->translator->trans('Logodatei') ])
            ->add('save', SubmitType::class, [ 'label' => $this->translator->trans('Speichern') ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Station::class,
        ]);
    }
}
