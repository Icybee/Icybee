declare namespace Icybee {

    class AdjustPopover extends Brickrouge.Popover {
        static readonly UpdateEvent: AdjustPopover.UpdateEvent
        static readonly LayoutEvent: AdjustPopover.LayoutEvent
        readonly adjust: Icybee.Adjust
        value: string|number|null
        observeUpdate(callback: Function)
        observeLayout(callback: Function)
    }

    namespace AdjustPopover {

        interface UpdateEvent {
            value: string|number|null
        }

        interface LayoutEvent {
        }

    }

}
