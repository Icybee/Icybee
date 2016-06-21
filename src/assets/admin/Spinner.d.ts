declare namespace Icybee {

    interface Spinner {
        constructor(element: Element, options?: Object): Spinner
        open(): void
        close(): void
        element: Element
        options: Object
        control: Element
        content: Element|null
        popover: Element|null
        resetValue: any
        value: any
    }

}
