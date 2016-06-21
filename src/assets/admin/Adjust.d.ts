declare namespace Icybee {

    class Adjust extends Brickrouge.Subject {
        observeChange(callback: Function)
        value: string|number|null
        static value: Adjust.ChangeEvent
    }

    namespace Adjust {

        interface ChangeEvent {
            target: Adjust
            value: string|number|null
        }

    }

}
