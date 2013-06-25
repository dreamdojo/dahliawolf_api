<?
class Geography_Controller extends _Controller {
	public function get_countries() {
		$this->load('Country');
		
		$data = $this->Country->get_countries();
		
		return static::wrap_result(true, $data);
	}
	
	public function get_states() {
		$this->load('State');
		
		$data = $this->State->get_states();
		
		return static::wrap_result(true, $data);
	}
}
?>