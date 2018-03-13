declare namespace Icybee {

    class Adjust extends Brickrouge.Subject {
        static readonly ChangeEvent: Adjust.ChangeEvent
        static readonly LayoutEvent: Adjust.LayoutEvent
        value: string|number|null
        observeChange(callback: Function)
        observeLayout(callback: Function)
    }

    namespace Adjust {

        interface ChangeEvent {
            target: Adjust
            value: string|number|null
        }

        interface LayoutEvent {
            target: Adjust
        }

    }

}
