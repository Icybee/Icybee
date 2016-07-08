declare namespace Icybee {

    class Spinner {
        constructor(element: Element, options?: Object)
        open(): void
        close(): void
        readonly element: Element
        readonly options: Object
        readonly control: Element
        readonly content: Element|null
        readonly popover: Element|null
        resetValue: any
        value: any
    }

}
