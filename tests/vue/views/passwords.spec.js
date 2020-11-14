import {shallowMount} from '@vue/test-utils';
import Passwords from '../../../resources/js/views/Profile/Passwords';
import expect from 'expect';
import vue from "vue";

describe('Passwords', () => {
    let wrapper;

    beforeEach(() => {
        wrapper = shallowMount(Passwords, {
            stubs: {
                TextInput: true,
                SuccessMessage: true
            }
        });
    });
    it('shows password update and delete if user has password', async () => {
        wrapper.setData({
            authUser: {
                has_password: true
            }
        });
        await vue.nextTick();
        expect(wrapper.html()).toContain("You currently have a secondary password set");
        expect(wrapper.findAll('textinput-stub').length).toBe(3);
    });

    it('shows password add if user does not have secondary password', async () => {
        wrapper.setData({
            authUser: {
                has_password: false
            }
        });
        await vue.nextTick();
        expect(wrapper.html()).toContain("You do not currently have a secondary password set. Add one below:");
        expect(wrapper.findAll('textinput-stub').length).toBe(2);
    });
    it('shows a success message', async () => {
        wrapper.setData({
            success: "Secondary Password Set!"
        });
        await vue.nextTick();
        expect(wrapper.find('successmessage-stub').exists()).toBeTruthy();
        expect(wrapper.find('successmessage-stub').text()).toBe("Secondary Password Set!");
    });
});
