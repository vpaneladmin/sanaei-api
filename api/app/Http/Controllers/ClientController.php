<?php

namespace App\Http\Controllers;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ClientController extends Controller
{
    public function inbound($id)
    {
        $inbound = Inbound::find($id);
        $data = [];
        if ($inbound) {
            $data['status'] = 'success';

            $clients = collect();
            $jClients = json_decode($inbound->settings);

            foreach ($jClients->clients as $item) {
                $cl = new \stdClass;
                $cl->uid = $item->id;
                $cl->username = $item->email;
                $cl->enable = $item->enable;

                $clients->push($cl);
            }

            $data['clients'] = $clients;
        } else {
            $data['status'] = 'failed';
        }

        return response()->json($data);
    }

    public function edit($uid, Request $req)
    {
        $inbounds = json_decode($req->input('inbounds'));
        $inputs = $req->all();
        $data = [];

        $found = 0;

        $data['status'] = 'success';
        $data['inbounds'] = [];
        $data['client'] = [];

        foreach ($inbounds as $inb) {
            $inbound = Inbound::find($inb);
            if ($inbound) {
                $settings = $inbound->settings;
                $jClients = json_decode($settings);
                $clients = collect();

                foreach ($jClients->clients as $item) {
                    if ($item->id == $uid) {
                        if (isset($inputs['enable'])) {
                            $item->enable = (bool) $inputs['enable'];
                        }
                        if (isset($inputs['username'])) {
                            $item->email = $inputs['username'];
                        }
                        if (isset($inputs['new_uid'])) {
                            $item->id = $inputs['new_uid'];
                        }

                        $cl = new \stdClass;
                        $cl->inbound = $inb;
                        $cl->uid = $item->id;
                        $cl->username = $item->email;
                        $cl->enable = $item->enable;

                        array_push($data['inbounds'], $inb);
                        array_push($data['client'], $cl);

                        $found += 1;
                    }

                    $clients->push($item);
                }

                $jClients->clients = $clients;

                $inbound->update([
                    'settings' => json_encode($jClients, JSON_PRETTY_PRINT),
                ]);
            }
        }

        if ($found == 0) {
            $data['status'] = 'not-found';
            unset($data['inbounds']);
            unset($data['client']);
        }

        return response()->json($data);
    }

    public function delete($uid, Request $req)
    {
        $inbounds = json_decode($req->input('inbounds'));

        foreach ($inbounds as $inb) {
            $inbound = Inbound::find($inb);
            if ($inbound) {
                $settings = $inbound->settings;
                $jClients = json_decode($settings);
                $clients = collect();

                foreach ($jClients->clients as $item) {
                    if ($item->id != $uid) {
                        $clients->push($item);
                    }
                }

                $jClients->clients = $clients;

                $inbound->update([
                    'settings' => json_encode($jClients, JSON_PRETTY_PRINT),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Client Removed!',
        ]);
    }

    public function multi(Request $req)
    {
        $inputs = $req->all();
        if (isset($inputs['clients']) && isset($inputs['inbounds'])) {
            $iClients = json_decode($inputs['clients']);
            $inbounds = json_decode($inputs['inbounds']);
            foreach ($inbounds as $inb) {
                $inbound = Inbound::find($inb);
                $settings = $inbound->settings;
                $jClients = json_decode($settings);
                $clients = collect();

                foreach ($jClients->clients as $item) {
                    foreach ($iClients as $cll) {
                        if ($item->email == $cll) {
                            if (isset($inputs['enable'])) {
                                $item->enable = (bool) $inputs['enable'];
                            }
                        }
                    }

                    $clients->push($item);
                }

                $jClients->clients = $clients;

                $inbound->update([
                    'settings' => json_encode($jClients, JSON_PRETTY_PRINT),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => count($iClients).' Clients Edited!',
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
            ]);
        }
    }

    public function create(Request $req)
    {
        $inputs = $req->all();
        if (isset($inputs['uid']) && isset($inputs['username']) && isset($inputs['inbounds'])) {
            $inbounds = json_decode($inputs['inbounds']);
            $success = 0;
            foreach ($inbounds as $inb) {
                $inbound = Inbound::find($inb);
                if ($inbound) {
                    $settings = $inbound->settings;
                    $jClients = json_decode($settings);
                    $clients = $jClients->clients;

                    $found = 0;
                    foreach ($clients as $item) {
                        if ($item->id == $inputs['uid']) {
                            $found++;
                        }
                    }

                    if ($found == 0) {
                        $newClient = new \stdClass;
                        $newClient->email = $inputs['username'];
                        $newClient->enable = (bool) $inputs['status'];
                        $newClient->expiryTime = 0;
                        $newClient->flow = '';
                        $newClient->id = $inputs['uid'];
                        $newClient->limitIp = 0;
                        $newClient->reset = 0;
                        $newClient->subId = str_split(md5($inputs['username']), 16)[0];
                        $newClient->tgId = '';
                        $newClient->totalGB = 0;

                        // Write to config.json
                        $path = Config::get('app.xconfig');
                        $file = file_get_contents($path);
                        $data = json_decode($file);
                        foreach ($data->inbounds as $inb) {
                            if ($inb->tag == $inbound->tag && isset($inb->settings->clients)) {
                                $obj = new \stdClass;
                                $obj->email = $newClient->email;
                                $obj->flow = '';
                                $obj->id = $newClient->id;

                                array_push($inb->settings->clients, $obj);
                            }
                        }
                        $newData = json_encode($data, JSON_PRETTY_PRINT);
                        $orgFile = fopen($path, 'w');
                        fwrite($orgFile, $newData);
                        fclose($orgFile);

                        exec('sudo systemctl restart x-ui');

                        ClientTraffic::create([
                            'inbound_id' => $inbound->id,
                            'enable' => 1,
                            'email' => $inputs['username'],
                            'up' => 0,
                            'down' => 0,
                            'expiry_time' => 0,
                            'total' => 0,
                            'reset' => 0,
                        ]);

                        array_push($clients, $newClient);

                        $success++;
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'UID Duplicate!',
                        ]);
                    }

                    $jClients->clients = $clients;

                    $inbound->update([
                        'settings' => json_encode($jClients, JSON_PRETTY_PRINT),
                    ]);
                }
            }

            if ($success > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Client added!',
                    'client' => $newClient,
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
            ]);
        }
    }

    public function traffic($uid, $inb)
    {
        $inbound = Inbound::find($inb);
        if ($inbound) {
            $settings = $inbound->settings;
            $jClients = json_decode($settings);

            foreach ($jClients->clients as $item) {
                if ($item->id == $uid) {
                    $username = $item->email;
                }
            }

            $traffic = ClientTraffic::where('email', $username)->first();

            if ($traffic) {
                return response()->json([
                    'status' => 'success',
                    'data' => $traffic,
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
            ]);
        }
    }

    public function disable($uid, $inb)
    {
        $inbound = Inbound::find($inb);
        if ($inbound) {
            $success = 0;

            $settings = $inbound->settings;
            $jClients = json_decode($settings);
            $clients = collect();

            foreach ($jClients->clients as $client) {
                if ($client->id == $uid) {
                    $client->enable = false;
                    $success = 1;
                }

                $clients->push($client);
            }

            $jClients->clients = $clients;

            $inbound->update([
                'settings' => json_encode($jClients, JSON_PRETTY_PRINT),
            ]);

            // Write to config.json
            $path = Config::get('app.xconfig');
            $file = file_get_contents($path);
            $data = json_decode($file);
            foreach ($data->inbounds as $inb) {
                if ($inb->tag == $inbound->tag && isset($inb->settings->clients)) {
                    foreach ($inb->settings->clients as $index => $client) {
                        if ($client->id == $uid) {
                            array_splice($inb->settings->clients, $index, 1);
                        }
                    }
                }
            }
            $newData = json_encode($data, JSON_PRETTY_PRINT);
            $orgFile = fopen($path, 'w');
            fwrite($orgFile, $newData);
            fclose($orgFile);

            exec('sudo systemctl restart x-ui');

            if ($success == 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Client disabled!',
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client not found!',
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Inbound not found!',
            ]);
        }
    }

    public function enable($uid, $inb)
    {
        $inbound = Inbound::find($inb);
        if ($inbound) {
            $success = 0;

            $settings = $inbound->settings;
            $jClients = json_decode($settings);
            $clients = collect();

            foreach ($jClients->clients as $client) {
                if ($client->id == $uid) {
                    $client->enable = true;
                    $success = 1;

                    // Write to config.json
                    $path = Config::get('app.xconfig');
                    $file = file_get_contents($path);
                    $data = json_decode($file);
                    foreach ($data->inbounds as $inb) {
                        if ($inb->tag == $inbound->tag && isset($inb->settings->clients)) {
                            $obj = new \stdClass;
                            $obj->email = $client->email;
                            $obj->flow = '';
                            $obj->id = $client->id;

                            array_push($inb->settings->clients, $obj);
                        }
                    }
                    $newData = json_encode($data, JSON_PRETTY_PRINT);
                    $orgFile = fopen($path, 'w');
                    fwrite($orgFile, $newData);
                    fclose($orgFile);

                    exec('sudo systemctl restart x-ui');
                }

                $clients->push($client);
            }

            $jClients->clients = $clients;

            $inbound->update([
                'settings' => json_encode($jClients, JSON_PRETTY_PRINT),
            ]);

            if ($success == 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Client enabled!',
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client not found!',
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Inbound not found!',
            ]);
        }
    }
}
