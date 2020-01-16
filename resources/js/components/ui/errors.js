export default class Errors {
    /**
     * Create a new Errors instance.
     */
    constructor() {
        this.errors = {};
    }


    /**
     * Determine if an errors exists for the given field.
     *
     * @param {string} field
     */
    has(field) {
        return this.errors.hasOwnProperty(field);
    }


    /**
     * Determine if we have any errors.
     */
    any() {
        return Object.keys(this.errors).length > 0 || typeof this.errors !== 'object';
    }


    /**
     * Retrieve the error message for a field.
     *
     * @param {string} field
     */
    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0];
        }
    }


    /**
     * Record the validation errors only.
     *
     * @param {object} errors
     */
    recordValidationErrors(errors) {
        errors.forEach(function (error) {
            if (error.extensions.category === "validation") {
                if (Array.isArray(error.extensions.validation)) {
                    this.errors = {0: error.extensions.validation}
                } else {
                    this.errors = error.extensions.validation;
                }
            }
        }.bind(this));
    }

    /**
     * Record an array of error messages
     *
     * @param [String] errors
     */
    record(errors) {
        this.errors[0] = errors;
    }


    /**
     * Clear one or all error fields.
     *
     * @param {string|null} field
     */
    clear(field) {
        if (field) {
            delete this.errors[field];

            return;
        }

        this.errors = {};
    }

    /**
     * Return the number of error messages stored
     */
    count() {
        return Object.keys(this.errors).length;
    }

    /**
     * Return the object of errors
     */
    all() {
        return this.errors;
    }

    first() {
        if (this.errors[0] === "undefined") {
            return;
        }
        return this.errors[0][0]
    }
}
