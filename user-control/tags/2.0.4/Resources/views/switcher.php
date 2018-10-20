<?php
/**
 * Formulaire de bascule.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Plugins\UserControl\Partial\UserControlPartialView $this.
 */
?>

<div <?php $this->attrs(); ?>>
    <form <?php echo $this->getHtmlAttrs($this->get('form', [])); ?>>
        <?php wp_nonce_field('UserControlSwitcherForm'); ?>

        <?php
        echo field(
            'hidden',
            [
                'name'  => 'action',
                'value' => 'switch',
            ]
        );
        ?>

        <?php
        echo field(
            'hidden',
            [
                'name'  => 'id',
                'value' => $this->get('name'),
            ]
        );
        ?>

        <?php
        echo field(
            'select-js',
            $this->get('role', [])
        );
        ?>

        <?php
        echo field(
            'select-js',
            $this->get('user', [])
        );
        ?>
    </form>
</div>