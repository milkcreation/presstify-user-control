<?php
/**
 * Formulaire de bascule.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\View\ViewController $this
 */
?>
<div <?php echo $this->htmlAttrs($this->get('attrs', [])); ?>>
    <form <?php echo $this->htmlAttrs($this->get('form', [])); ?>>
        <?php wp_nonce_field('UserControlSwitcherForm', '_wpnonce', false); ?>

        <?php echo field('hidden', [
            'name'  => 'action',
            'value' => 'switch',
        ]); ?>

        <?php echo field('hidden', [
            'name'  => 'id',
            'value' => $this->get('name'),
        ]); ?>

        <?php echo field('select-js', $this->get('role', [])); ?>

        <?php echo field('select-js', $this->get('user', [])); ?>
    </form>
</div>