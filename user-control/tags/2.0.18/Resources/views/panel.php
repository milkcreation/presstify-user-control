<?php
/**
 * Panneau de prise de contrôle.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\View\ViewController $this
 */
?>
<div <?php echo $this->htmlAttrs($this->get('attrs', [])); ?>>
    <button
            type="button" class="UserControlPanel-label"
            aria-control="user_control-panel_toggle"
            data-target="#<?php echo $this->get('attrs.id'); ?>"
    >
        <span class="UserControlPanel-labelTxt">
            <?php _e('Prise de contrôle', 'tify'); ?>
        </span>
    </button>

    <div class="UserControlPanel-container UserControlPanel-container--<?php echo $this->get('auth'); ?>">
        <?php switch ($this->get('auth')) :
            case 'switch' :
                echo user_control_switcher($this->get('name'), $this->get('switcher', []));
                break;
            case 'restore' :
                echo user_control_trigger($this->get('name'), $this->get('trigger', []));
                break;
        endswitch; ?>
    </div>
</div>