declare namespace Icybee {

    interface AdjustPopover extends Brickrouge.Popover {
        adjust: Icybee.Adjust
        value: string|number|null
    }

    namespace AdjustPopover {

        interface ActionEvent extends Brickrouge.Popover.ActionEvent {
            action: string
            popover: AdjustPopover
        }

    }

}
